<?php

namespace App\Controllers;

use App\Libraries\NeoFeeder;
use App\Libraries\PisnService;
use App\Libraries\WizardProgress;

/**
 * PISN graduation wizard.
 *
 * Uploads a list of prospective graduates via Excel, then walks the admin through
 * a sequential, manual verification of each student (identity -> academic ->
 * PISN eligibility -> graduation input). Progress survives auth-session expiry via
 * the WizardProgress cache store + the wizard resume cookie (ENH-016). At the end
 * it submits each verified student to Neo Feeder via InsertMahasiswaLulusDO.
 */
class Graduation extends BaseController
{
    /**
     * Excel column -> internal key map (header row is matched case-insensitively).
     */
    private const EXCEL_HEADERS = [
        'nim'            => 'nim',
        'nama'           => 'nama',
        'jenis_keluar'   => 'jenis_keluar',
        'tgl_keluar'     => 'tgl_keluar',
        'periode_keluar' => 'periode_keluar',
        'ipk'            => 'ipk',
    ];

    private WizardProgress $wizard;
    private NeoFeeder $neoFeeder;
    private PisnService $pisn;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        $this->wizard    = service('wizardProgress');
        $this->neoFeeder = service('neoFeeder');
        $this->pisn      = service('pisn');
    }

    private function currentToken(): ?string
    {
        return service('auth')->getWizardResumeToken();
    }

    private function loadProgress(): ?array
    {
        $token = $this->currentToken();
        if ($token === null) {
            return null;
        }

        $state = $this->wizard->load($token);

        return is_array($state) ? $state : null;
    }

    private function render(string $view, array $data): string
    {
        $username = $data['username'] ?? service('auth')->getCurrentUser() ?? '';
        $title    = $data['title'] ?? 'PISN Graduation';

        return view('layout/header', ['username' => $username, 'title' => $title])
            . view('layout/sidebar')
            . view($view, $data)
            . view('layout/footer');
    }

    /**
     * GET /graduation — upload form (with optional resume prompt).
     */
    public function index()
    {
        $username = service('auth')->getCurrentUser() ?? '';
        $token    = $this->currentToken();
        $canResume = $token !== null && $this->wizard->load($token) !== null;

        return $this->render('graduation/upload', [
            'username'  => $username,
            'canResume' => $canResume,
            'error'     => session()->getFlashdata('graduation_error'),
        ]);
    }

    /**
     * POST /graduation/upload — parse Excel, seed wizard progress, set resume cookie.
     */
    public function upload()
    {
        $file = $this->request->getFile('excel');
        if ($file === null || ! $file->isValid()) {
            return redirect()->back()
                ->with('graduation_error', 'Unggah file Excel (.xlsx) terlebih dahulu.')
                ->withInput();
        }

        if (strtolower($file->getExtension()) !== 'xlsx') {
            return redirect()->back()
                ->with('graduation_error', 'File harus berformat .xlsx (Excel 2007+).')
                ->withInput();
        }

        try {
            $students = $this->parseExcel($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('graduation_error', 'Gagal membaca Excel: ' . $e->getMessage())
                ->withInput();
        }

        if (count($students) === 0) {
            return redirect()->back()
                ->with('graduation_error', 'Tidak ada baris mahasiswa valid di Excel (kolom "nim" wajib).')
                ->withInput();
        }

        $token  = $this->wizard->generateToken();
        $state  = ['students' => $students, 'current' => 0, 'results' => []];
        $this->wizard->save($token, $state);
        service('auth')->setWizardResumeCookie($token);

        return redirect()->to('graduation/step');
    }

    /**
     * GET /graduation/resume — continue an interrupted wizard.
     */
    public function resume()
    {
        $token = $this->currentToken();
        if ($token === null || $this->wizard->load($token) === null) {
            return redirect()->to('graduation');
        }

        return redirect()->to('graduation/step');
    }

    /**
     * POST /graduation/cancel — clear an interrupted wizard session.
     */
    public function cancel()
    {
        $token = $this->currentToken();
        if ($token !== null) {
            $this->wizard->clear($token);
            service('auth')->clearWizardResumeCookie();
        }

        return redirect()->to('graduation');
    }

    /**
     * GET /graduation/step — show the current student verification screen.
     */
    public function step()
    {
        $progress = $this->loadProgress();
        if ($progress === null) {
            return redirect()->to('graduation');
        }

        $username = service('auth')->getCurrentUser() ?? '';
        $idx      = $progress['current'];
        $student  = $progress['students'][$idx];
        $apiToken = session('auth.token');

        $identity    = null;
        $academic    = null;
        $transcript  = null;
        $completeness = null;
        $error       = null;

        if ($apiToken !== null) {
            $nimSafe = str_replace("'", "\'", (string) $student['nim']);

            $idResp = $this->neoFeeder->getListMahasiswa($apiToken, [
                'filter' => "nim='{$nimSafe}'",
                'limit'  => 1,
            ]);
            if (($idResp['error_code'] ?? -1) === 0 && isset($idResp['data'][0])) {
                $identity = $idResp['data'][0];
            } else {
                $error = $idResp['error_msg'] ?? 'Gagal memuat data mahasiswa.';
            }

            $acResp = $this->neoFeeder->getAktivitasKuliahMahasiswa($apiToken, [
                'filter' => "nim='{$nimSafe}'",
                'limit'  => 50,
            ]);
            if (($acResp['error_code'] ?? -1) === 0 && isset($acResp['data'])) {
                $academic = $acResp['data'];
            }

            if ($identity !== null && isset($identity['id_registrasi_mahasiswa'])) {
                $idReg = str_replace("'", "\'", (string) $identity['id_registrasi_mahasiswa']);
                $trResp = $this->neoFeeder->getTranskripMahasiswa($apiToken, [
                    'filter' => "id_registrasi_mahasiswa='{$idReg}'",
                    'limit'  => 200,
                ]);
                if (($trResp['error_code'] ?? -1) === 0 && isset($trResp['data'])) {
                    $transcript   = $trResp['data'];
                    $completeness = $this->checkTranscriptCompleteness($transcript);
                }
            }
        } else {
            $error = 'Sesi token habis; silakan login ulang, lalu klik Lanjutkan.';
        }

        $pisn = $this->pisn->checkEligibility($student);
        $total = count($progress['students']);

        return $this->render('graduation/wizard', [
            'username'     => $username,
            'index'        => $idx,
            'total'        => $total,
            'student'      => $student,
            'identity'     => $identity,
            'academic'     => $academic,
            'transcript'   => $transcript,
            'completeness' => $completeness,
            'pisn'         => $pisn,
            'uuidKeys'     => ['id_registrasi_mahasiswa', 'id_mahasiswa', 'id_aktivitas_kuliah'],
            'uuidRegex'    => '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            'error'        => $error,
            'isLast'       => ($idx === $total - 1),
            'saved'        => $student['saved'] ?? false,
        ]);
    }

    /**
     * Checks whether a student's transcript is complete for graduation.
     *
     * The GetTranskripMahasiswa response carries per-course final grades
     * (nilai_angka / nilai_huruf / nilai_indeks) and does NOT include an
     * id_jenis_mata_kuliah type flag, so the thesis/skripsi course is detected
     * by its course name. A transcript is complete when it is non-empty and
     * contains a thesis-named row with a non-empty grade.
     *
     * The thesis-name pattern is a heuristic; confirm the exact course naming
     * against a graduating student's transcript and tighten the pattern if needed.
     *
     * @param array $transcript Rows from GetTranskripMahasiswa.
     *
     * @return array{complete:bool, reason:string}
     */
    private function checkTranscriptCompleteness(array $transcript): array
    {
        if (empty($transcript)) {
            return ['complete' => false, 'reason' => 'Transkrip kosong (tidak ada nilai).'];
        }

        $thesisPattern = '/skripsi|tugas\s*akhir|thesis|disertasi/i';
        foreach ($transcript as $row) {
            $name = $row['nama_mata_kuliah'] ?? '';
            if (preg_match($thesisPattern, $name)) {
                $nilai = $row['nilai_huruf'] ?? ($row['nilai_angka'] ?? null);
                if ($nilai !== null && $nilai !== '') {
                    return ['complete' => true, 'reason' => ''];
                }
            }
        }

        return ['complete' => false, 'reason' => 'Nilai skripsi/tugas akhir belum terdeteksi di transkrip.'];
    }

    /**
     * POST /graduation/step — save verification for the current student and advance.
     */
    public function stepPost()
    {
        $progress = $this->loadProgress();
        if ($progress === null) {
            return redirect()->to('graduation');
        }

        $token = $this->currentToken();
        $idx   = $progress['current'];
        $student = $progress['students'][$idx];

        $identityOk    = $this->request->getPost('identity_ok') === '1';
        $academicFlag  = trim((string) $this->request->getPost('academic_flag'));
        $pisnOk       = $this->request->getPost('pisn_ok') === '1';
        $biayaKuliah  = trim((string) $this->request->getPost('biaya_kuliah'));

        $nim           = trim((string) $this->request->getPost('nim'));
        $nama          = trim((string) $this->request->getPost('nama'));
        $jenisKeluar   = trim((string) $this->request->getPost('jenis_keluar'));
        $tglKeluar     = trim((string) $this->request->getPost('tgl_keluar'));
        $periodeKeluar = trim((string) $this->request->getPost('periode_keluar'));
        $ipk           = trim((string) $this->request->getPost('ipk'));
        $noIjazah      = trim((string) $this->request->getPost('no_ijazah'));
        if ($noIjazah === '') {
            $noIjazah = '-';
        }

        $errors = [];
        if (! $identityOk) {
            $errors[] = 'Centang konfirmasi identitas (cocok dengan KTP).';
        }
        if ($nim === '' || $nama === '' || $jenisKeluar === '' || $tglKeluar === '' || $periodeKeluar === '' || $ipk === '') {
            $errors[] = 'Field kelulusan wajib diisi (No Ijazah otomatis "-").';
        }
        if (! $pisnOk) {
            $errors[] = 'Centang konfirmasi eligibilitas PISN (manual).';
        }
        // ponytail: editing academics requires Biaya Kuliah Semester filled (deferred Update).
        if ($academicFlag !== '' && $biayaKuliah === '') {
            $errors[] = 'Bila ada catatan akademik, isi Biaya Kuliah Semester.';
        }

        if (count($errors) > 0) {
            return redirect()->back()
                ->with('graduation_error', implode(' ', $errors))
                ->withInput();
        }

        $student['saved']          = true;
        $student['identity_ok']    = $identityOk;
        $student['academic_flag']  = $academicFlag;
        $student['pisn_ok']        = $pisnOk;
        $student['graduation']     = [
            'nim'            => $nim,
            'nama'           => $nama,
            'jenis_keluar'   => $jenisKeluar,
            'tgl_keluar'     => $tglKeluar,
            'periode_keluar' => $periodeKeluar,
            'ipk'            => $ipk,
            'no_ijazah'      => $noIjazah,
        ];

        $progress['students'][$idx] = $student;
        $isLast = ($idx === count($progress['students']) - 1);

        if ($isLast) {
            $progress['current'] = $idx + 1;
            $this->wizard->save($token, $progress);

            return redirect()->to('graduation/preview');
        }

        $progress['current'] = $idx + 1;
        $this->wizard->save($token, $progress);

        return redirect()->to('graduation/step');
    }

    /**
     * Submits every verified student to Neo Feeder, then shows guidance.
     */
    public function finish(): string
    {
        $token    = $this->currentToken();
        $progress = $this->loadProgress();
        if ($progress === null) {
            return redirect()->to('graduation');
        }

        $apiToken = session('auth.token');
        $results  = [];

        if ($apiToken !== null) {
            foreach ($progress['students'] as $student) {
                if (empty($student['saved']) || empty($student['graduation'])) {
                    $results[] = ['nim' => $student['nim'], 'success' => false, 'msg' => 'Belum diverifikasi.'];
                    continue;
                }

                $g = $student['graduation'];
                // ponytail: record keys pending live GetListMahasiswaLulusDO schema confirmation.
                $record = [
                    'nim'            => $g['nim'],
                    'nama'           => $g['nama'],
                    'jenis_keluar'   => $g['jenis_keluar'],
                    'tgl_keluar'     => $g['tgl_keluar'],
                    'periode_keluar' => $g['periode_keluar'],
                    'ipk'            => $g['ipk'],
                    'no_ijazah'      => $g['no_ijazah'],
                ];

                $resp = $this->neoFeeder->insertMahasiswaLulusDO($apiToken, $record);
                if (($resp['error_code'] ?? -1) === 0) {
                    $results[] = ['nim' => $g['nim'], 'success' => true, 'msg' => 'Terkirim ke Neo Feeder.'];
                } else {
                    $results[] = ['nim' => $g['nim'], 'success' => false, 'msg' => $resp['error_msg'] ?? 'Gagal mengirim.'];
                }
            }
        } else {
            foreach ($progress['students'] as $student) {
                $results[] = ['nim' => $student['nim'], 'success' => false, 'msg' => 'Sesi token habis; login lalu resume.'];
            }
        }

        if ($token !== null) {
            $this->wizard->clear($token);
            service('auth')->clearWizardResumeCookie();
        }

        session()->setFlashdata('graduation_results', $results);

        return redirect()->to('graduation/guidance');
    }

    /**
     * GET /graduation/preview — preview all verified students before submission.
     */
    public function preview()
    {
        $progress = $this->loadProgress();
        if ($progress === null) {
            return redirect()->to('graduation');
        }

        $username = service('auth')->getCurrentUser() ?? '';

        return $this->render('graduation/preview', [
            'username' => $username,
            'students' => $progress['students'],
        ]);
    }

    /**
     * GET /graduation/guidance — post-submission guidance.
     */
    public function guidance()
    {
        $results = session()->getFlashdata('graduation_results') ?? [];

        return $this->render('graduation/guidance', [
            'username' => service('auth')->getCurrentUser() ?? '',
            'results'  => $results,
        ]);
    }

    /**
     * GET /graduation/template — download Excel template for PISN graduation upload.
     */
    public function downloadTemplate(): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['nim', 'nama', 'jenis_keluar', 'tgl_keluar', 'periode_keluar', 'ipk'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($columns[$i] . '1', $header);
        }

        $example = ['12345678', 'Nama Mahasiswa', 'Lulus', '2026-08-31', '2026.1', '3.75'];
        foreach ($example as $i => $value) {
            $sheet->setCellValue($columns[$i] . '2', $value);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'pisn_graduation_template.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Parses the uploaded Excel file into a list of student records.
     *
     * @param string $path Absolute path to the uploaded temp file.
     *
     * @return list<array<string, mixed>>
     */
    private function parseExcel(string $path): array
    {
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $rows        = $spreadsheet->getActiveSheet()->toArray();

        if (count($rows) < 2) {
            return [];
        }

        $header  = array_map(static fn ($c) => strtolower(trim((string) $c)), array_shift($rows));
        $colIndex = [];
        foreach (self::EXCEL_HEADERS as $key => $label) {
            $pos = array_search($label, $header, true);
            if ($pos !== false) {
                $colIndex[$key] = $pos;
            }
        }

        if (! isset($colIndex['nim'])) {
            return [];
        }

        $students = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $nim = trim((string) ($row[$colIndex['nim']] ?? ''));
            if ($nim === '') {
                continue;
            }
            $students[] = [
                'nim'            => $nim,
                'nama'           => isset($colIndex['nama']) ? trim((string) ($row[$colIndex['nama']] ?? '')) : '',
                'jenis_keluar'   => isset($colIndex['jenis_keluar']) ? trim((string) ($row[$colIndex['jenis_keluar']] ?? '')) : '',
                'tgl_keluar'     => isset($colIndex['tgl_keluar']) ? trim((string) ($row[$colIndex['tgl_keluar']] ?? '')) : '',
                'periode_keluar' => isset($colIndex['periode_keluar']) ? trim((string) ($row[$colIndex['periode_keluar']] ?? '')) : '',
                'ipk'            => isset($colIndex['ipk']) ? trim((string) ($row[$colIndex['ipk']] ?? '')) : '',
                'saved'          => false,
            ];
        }

        return $students;
    }
}
