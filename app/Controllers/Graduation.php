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
     *
     * Minimal template: NIM (lookup), Nama (optional KTP-name validation
     * source), Tanggal Keluar, IPK, and a letter grade for the thesis/skripsi
     * course (nilai_skripsi).
     * jenis_keluar is always "Lulus" and periode_keluar is derived from tgl_keluar.
     */
    private const EXCEL_HEADERS = [
        'nim'           => 'nim',
        'nama'          => 'nama',
        'tgl_keluar'    => 'tgl_keluar',
        'ipk'           => 'ipk',
        'nilai_skripsi' => 'nilai_skripsi',
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
        $error       = session()->getFlashdata('graduation_error');

        if ($apiToken !== null) {
            $nimSafe = str_replace("'", "\'", (string) $student['nim']);

            $idResp = $this->neoFeeder->getListMahasiswa($apiToken, [
                'filter' => "nim='{$nimSafe}'",
                'limit'  => 1,
            ]);
            if (($idResp['error_code'] ?? -1) === 0 && isset($idResp['data'][0])) {
                $identity = $idResp['data'][0];
            } elseif (empty($error)) {
                $error = $idResp['error_msg'] ?? 'Gagal memuat data mahasiswa.';
            }

            $acResp = $this->neoFeeder->getAktivitasKuliahMahasiswa($apiToken, [
                'filter' => "nim='{$nimSafe}'",
                'order'  => 'id_semester asc',
                'limit'  => 50,
            ]);
            if (($acResp['error_code'] ?? -1) === 0 && isset($acResp['data'])) {
                $academic = $acResp['data'];
            }
            if (is_array($academic)) {
                usort($academic, fn ($a, $b) => strcmp($a['id_semester'] ?? '', $b['id_semester'] ?? ''));
            }

            if (! empty($student['nim'])) {
                $trResp = $this->neoFeeder->getCekTranskripMahasiswa($apiToken, (string) $student['nim']);
                if (($trResp['error_code'] ?? -1) === 0 && isset($trResp['data'])) {
                    $transcript   = $trResp['data'];
                    $completeness = $this->checkTranscriptCompleteness($transcript);
                } elseif (($trResp['error_code'] ?? -1) !== 0 && empty($error)) {
                    $error = $trResp['error_msg'] ?? 'Gagal memuat data transkrip.';
                }
            }
        } else {
            $error = 'Sesi token habis; silakan login ulang, lalu klik Lanjutkan.';
        }

        $statusOptions = [];
        $stResp = $this->neoFeeder->getStatusMahasiswa($apiToken);
        if (($stResp['error_code'] ?? -1) === 0 && isset($stResp['data'])) {
            foreach ($stResp['data'] as $st) {
                $statusOptions[(string) $st['id_status_mahasiswa']] = trim((string) ($st['nama_status_mahasiswa'] ?? ''));
            }
        }

        $jenisKeluarOptions = [];
        $jkResp = $this->neoFeeder->getJenisKeluar($apiToken);
        if (($jkResp['error_code'] ?? -1) === 0 && isset($jkResp['data'])) {
            foreach ($jkResp['data'] as $jk) {
                $jenisKeluarOptions[(string) $jk['id_jenis_keluar']] = trim((string) ($jk['jenis_keluar'] ?? ''));
            }
        }

        $semesterOptions = [];
        // Raw semester rows retained for periode derivation (range + Pendek exclusion).
        $semesterRows = [];
        $smResp = $this->neoFeeder->getSemester($apiToken);
        if (($smResp['error_code'] ?? -1) === 0 && isset($smResp['data'])) {
            foreach ($smResp['data'] as $sm) {
                $semesterOptions[(string) $sm['id_semester']] = trim((string) ($sm['nama_semester'] ?? ''));
                $semesterRows[] = $sm;
            }
        }

        // ENH-007: only rows whose id_semester is currently active in the Daftar
        // Semester (a_periode_aktif) may be edited. The active set mutates.
        $activeSemesterIds = [];
        foreach ($semesterRows as $sm) {
            if (! empty($sm['a_periode_aktif'])) {
                $activeSemesterIds[] = (string) $sm['id_semester'];
            }
        }

        // ENH-005: derive periode_keluar from tgl_keluar by range-matching the
        // date against the semester reference list (Ganjil/Genap only, Pendek =
        // semester marker 3 excluded). Result is a default the admin may override.
        $tglForDerive = $student['graduation']['tgl_keluar'] ?? $student['tgl_keluar'] ?? '';
        $derivedPeriode = $this->derivePeriodeKeluar(['tgl_keluar' => $tglForDerive], $semesterRows);

        // ENH-005: jenis_keluar defaults to "Lulus" (PISN graduation is always
        // this jenis keluar) when the student record carries none.
        if (! isset($student['graduation']) || ! is_array($student['graduation'])) {
            $student['graduation'] = [
                'nim'            => $student['nim'],
                'nama'           => $student['nama'],
                'jenis_keluar'   => '',
                'tgl_keluar'     => $student['tgl_keluar'],
                'periode_keluar' => '',
                'ipk'            => $student['ipk'],
                'no_ijazah'      => '-',
            ];
        }
        $g = &$student['graduation'];
        if (empty($g['jenis_keluar'])) {
            $lulusKey = array_search('Lulus', $jenisKeluarOptions, true);
            if ($lulusKey !== false) {
                $g['jenis_keluar'] = (string) $lulusKey;
            }
        }
        if (empty($g['periode_keluar']) && $derivedPeriode !== null) {
            $g['periode_keluar'] = $derivedPeriode;
        }

        // ENH-006: the last academic row (largest id_semester) holds the "last
        // IPK" that is the single source shared with the step-5 graduation IPK
        // input. Excel IPK is only the initial default; the value is synced
        // two-ways in the view. Rows are already sorted ascending by id_semester.
        $lastAcademic = empty($academic) ? null : $academic[count($academic) - 1];
        $lastIsActive = $lastAcademic !== null
            && in_array((string) $lastAcademic['id_semester'], $activeSemesterIds, true);

        // ENH-008: server-side initial auto-check state for the step checkboxes.
        // Step 1 (identity) and step 3 (transcript) are static per render; step 2
        // (academic) is additionally recomputed real-time in the browser (JS).
        $excelIpk = $student['ipk'] ?? '';

        $activeCode = null;
        foreach ($statusOptions as $code => $label) {
            if (trim((string) $label) === 'Aktif') {
                $activeCode = (string) $code;
                break;
            }
        }

        // Step 1: auto-check only when the (optional) Excel name is present and
        // exactly equals the PDDIKTI registered name.
        $identityOk = $student['nama'] !== ''
            && ($identity['nama_mahasiswa'] ?? '') === $student['nama'];

        // Step 2: last row matches the Excel IPK and its status is Aktif.
        $academicOk = false;
        if ($lastAcademic !== null && $lastIsActive && $activeCode !== null) {
            $lastSmt = (string) $lastAcademic['id_semester'];
            $lastIpkVal = trim((string) ($student['academics'][$lastSmt]['ipk']
                ?? $lastAcademic['ipk'] ?? ''));
            $lastStatusVal = (string) ($student['academics'][$lastSmt]['id_status_mahasiswa']
                ?? $lastAcademic['id_status_mahasiswa'] ?? '');
            $academicOk = $lastIpkVal === trim((string) $excelIpk)
                && $lastStatusVal === (string) $activeCode;
        }

        // Step 3: a thesis course exists with a grade AND is included in the
        // transcript (choosed:true). choosed is informational for the completeness
        // badge but required for this auto-check gate (ENH-008 spec).
        $transcriptOk = false;
        if (! empty($completeness['complete'])) {
            foreach (($completeness['thesis'] ?? []) as $t) {
                if (! empty($t['hasGrade']) && ! empty($t['choosed'])) {
                    $transcriptOk = true;
                    break;
                }
            }
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
            'statusOptions' => $statusOptions,
            'jenisKeluarOptions' => $jenisKeluarOptions,
            'semesterOptions'    => $semesterOptions,
            'activeSemesterIds'  => $activeSemesterIds,
            'lastAcademic'       => $lastAcademic,
            'lastIsActive'       => $lastIsActive,
            'excelIpk'           => $excelIpk,
            'identityOk'         => $identityOk,
            'academicOk'         => $academicOk,
            'transcriptOk'       => $transcriptOk,
            'activeCode'         => $activeCode,
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
     * Completeness rule (per BUG-001): the thesis/skripsi/tugas akhir course
     * must exist in the transcript AND carry a non-empty grade. The `choosed`
     * marker (course "included in transcript" per the cloud Cek Transkrip
     * Mahasiswa menu) is informational only — it does NOT block completeness.
     *
     * The thesis course is detected by name
     * (/skripsi|tugas\s*akhir|thesis|disertasi/i) because the WS/cloud
     * transcript payloads carry no type flag.
     *
     * @param array $transcript Rows from getCekTranskripMahasiswa()
     *                          (cloud nilai_mahasiswa rows, kebab nm_mk/kode_mk/id_smt).
     *
     * @return array{complete:bool, reason:string, thesis:array}
     *               thesis = detail rows of each detected thesis course.
     */
    private function checkTranscriptCompleteness(array $transcript): array
    {
        if (empty($transcript)) {
            return [
                'complete' => false,
                'reason'   => 'Transkrip kosong (tidak ada nilai).',
                'thesis'   => [],
            ];
        }

        $thesisPattern = '/skripsi|tugas\s*akhir|thesis|disertasi/i';
        $thesisRows    = [];

        foreach ($transcript as $row) {
            $name = $row['nm_mk'] ?? ($row['nama_mata_kuliah'] ?? '');
            if (preg_match($thesisPattern, $name)) {
                $nilai     = $row['nilai_huruf'] ?? ($row['nilai_angka'] ?? null);
                $hasGrade  = ($nilai !== null && $nilai !== '');
                $thesisRows[] = [
                    'nama'     => $name,
                    'kode'     => $row['kode_mk'] ?? ($row['kode_mata_kuliah'] ?? ''),
                    'semester' => $row['id_smt'] ?? ($row['id_semester'] ?? ''),
                    'nilai'    => $nilai !== null && $nilai !== '' ? $nilai : null,
                    'hasGrade' => $hasGrade,
                    'choosed'  => ! empty($row['choosed']),
                ];
            }
        }

        if (empty($thesisRows)) {
            return [
                'complete' => false,
                'reason'   => 'MK skripsi/tugas akhir tidak ditemukan di transkrip.',
                'thesis'   => [],
            ];
        }

        foreach ($thesisRows as $t) {
            if ($t['hasGrade']) {
                return [
                    'complete' => true,
                    'reason'   => '',
                    'thesis'   => $thesisRows,
                ];
            }
        }

        return [
            'complete' => false,
            'reason'   => 'Nilai MK skripsi/tugas akhir belum tersedia.',
            'thesis'   => $thesisRows,
        ];
    }

    /**
     * Finds the thesis/skripsi course row that is registered but still carries
     * no grade, returning its composite key for UpdateNilaiPerkuliahanKelas.
     *
     * @param array $transcript Rows from getCekTranskripMahasiswa()
     *                          (cloud nilai_mahasiswa rows carrying id_kls / id_reg_pd).
     *
     * @return array|null The row's key as ['id_reg_pd', 'id_kls'], or null when
     *                    no graded-missing thesis row exists.
     */
    private function findMissingGradeThesis(array $transcript): ?array
    {
        if (empty($transcript)) {
            return null;
        }

        $thesisPattern = '/skripsi|tugas\s*akhir|thesis|disertasi/i';

        foreach ($transcript as $row) {
            $name = $row['nm_mk'] ?? ($row['nama_mata_kuliah'] ?? '');
            if (! preg_match($thesisPattern, $name)) {
                continue;
            }
            $nilai    = $row['nilai_huruf'] ?? ($row['nilai_angka'] ?? null);
            $hasGrade = ($nilai !== null && $nilai !== '');
            if ($hasGrade) {
                continue;
            }
            $idReg = $row['id_reg_pd'] ?? ($row['id_registrasi_mahasiswa'] ?? '');
            $idKls = $row['id_kls'] ?? ($row['id_kelas_kuliah'] ?? '');
            if ($idReg === '' || $idKls === '') {
                continue;
            }
            return ['id_reg_pd' => (string) $idReg, 'id_kls' => (string) $idKls];
        }

        return null;
    }

    /**
     * Derives the periode keluar (id_semester) from a student's tgl_keluar by
     * range-matching the date against the Daftar Semester (GetSemester) rows.
     *
     * Only Ganjil/Genap semesters are eligible — Pendek semesters (marker
     * `semester` == 3) are excluded from the choices. Returns the first matching
     * id_semester, or null when no range fits (caller leaves the field for manual
     * selection). ENH-005.
     *
     * @param array       $student       Wizard student record.
     * @param list<array> $semesterRows  Raw GetSemester rows.
     */
    private function derivePeriodeKeluar(array $student, array $semesterRows): ?string
    {
        $tglKeluar = trim((string) ($student['tgl_keluar'] ?? ''));
        if ($tglKeluar === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglKeluar) !== 1) {
            return null;
        }

        $date = strtotime($tglKeluar);
        if ($date === false) {
            return null;
        }

        foreach ($semesterRows as $sm) {
            // Pendek (marker 3) semesters are not eligible periode choices.
            if ((string) ($sm['semester'] ?? '') === '3') {
                continue;
            }
            $mulai = strtotime((string) ($sm['tanggal_mulai'] ?? ''));
            $selesai = strtotime((string) ($sm['tanggal_selesai'] ?? ''));
            if ($mulai !== false && $selesai !== false && $date >= $mulai && $date <= $selesai) {
                return (string) $sm['id_semester'];
            }
        }

        return null;
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
        $academicOk    = $this->request->getPost('academic_ok') === '1';
        $transcriptOk  = $this->request->getPost('transcript_ok') === '1';
        $pisnOk       = $this->request->getPost('pisn_ok') === '1';
        $academics    = $this->request->getPost('academics') ?? [];

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
        if (! $academicOk) {
            $errors[] = 'Centang konfirmasi akademik (IPK semester terakhir sesuai & status Aktif).';
        }
        if (! $transcriptOk) {
            $errors[] = 'Centang konfirmasi kelengkapan transkrip (MK skripsi bernilai & masuk transkrip).';
        }
        if ($nim === '' || $nama === '' || $jenisKeluar === '' || $tglKeluar === '' || $periodeKeluar === '' || $ipk === '') {
            $errors[] = 'Field kelulusan wajib diisi (No Ijazah otomatis "-").';
        }
        if (! $pisnOk) {
            $errors[] = 'Centang konfirmasi eligibilitas PISN (manual).';
        }

        if (count($errors) > 0) {
            return redirect()->back()
                ->with('graduation_error', implode(' ', $errors))
                ->withInput();
        }

        $student['saved']          = true;
        $student['identity_ok']    = $identityOk;
        $student['academic_ok']    = $academicOk;
        $student['transcript_ok']  = $transcriptOk;
        $student['pisn_ok']        = $pisnOk;
        $student['academics']      = $academics;
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

                // Resolve the required id_registrasi_mahasiswa (PK) via the student's academic rows.
                $nimSafe = trim((string) ($student['nim'] ?? $g['nim']));
                $acResp  = $this->neoFeeder->getAktivitasKuliahMahasiswa($apiToken, [
                    'filter' => "nim='{$nimSafe}'",
                    'limit'  => 1,
                ]);
                $idReg = ($acResp['error_code'] ?? -1) === 0
                    ? ($acResp['data'][0]['id_registrasi_mahasiswa'] ?? '')
                    : '';
                if ($idReg === '') {
                    $results[] = ['nim' => $g['nim'], 'success' => false, 'msg' => 'Gagal me-resolve id_registrasi_mahasiswa.'];
                    continue;
                }

                // Record keys confirmed live via GetDictionary (InsertMahasiswaLulusDO).
                $record = [
                    'id_registrasi_mahasiswa' => (string) $idReg,
                    'id_jenis_keluar'          => (string) $g['jenis_keluar'],
                    'tanggal_keluar'           => (string) $g['tgl_keluar'],
                    'id_periode_keluar'        => (string) $g['periode_keluar'],
                    'ipk'                      => (string) $g['ipk'],
                    'nomor_ijazah'             => (string) $g['no_ijazah'],
                ];

                $resp = $this->neoFeeder->insertMahasiswaLulusDO($apiToken, $record);
                if (($resp['error_code'] ?? -1) === 0) {
                    $results[] = ['nim' => $g['nim'], 'success' => true, 'msg' => 'Terkirim ke Neo Feeder.'];
                } else {
                    $results[] = ['nim' => $g['nim'], 'success' => false, 'msg' => $resp['error_msg'] ?? 'Gagal mengirim.'];
                }

                // Push per-semester academic corrections (status/ipk/ips) to Neo Feeder.
                $edits = $student['academics'] ?? [];
                if (count($edits) > 0) {
                    $nimSafe = trim((string) ($student['nim'] ?? $g['nim']));
                    $acResp  = $this->neoFeeder->getAktivitasKuliahMahasiswa($apiToken, [
                        'filter' => "nim='{$nimSafe}'",
                        'limit'  => 50,
                    ]);
                    if (($acResp['error_code'] ?? -1) === 0 && isset($acResp['data'])) {
                        $regByIdSemester = [];
                        foreach ($acResp['data'] as $r) {
                            $regByIdSemester[$r['id_semester']] = $r['id_registrasi_mahasiswa'];
                        }
                        foreach ($edits as $idSemester => $edit) {
                            if (! isset($regByIdSemester[$idSemester])) {
                                continue;
                            }
                            $akRecord = [];
                            foreach (['id_status_mahasiswa', 'ips', 'ipk'] as $field) {
                                if (isset($edit[$field]) && (string) $edit[$field] !== '') {
                                    $akRecord[$field] = $edit[$field];
                                }
                            }
                            if (count($akRecord) > 0) {
                                $this->neoFeeder->updatePerkuliahanMahasiswa(
                                    $apiToken,
                                    (string) $regByIdSemester[$idSemester],
                                    (string) $idSemester,
                                    $akRecord
                                );
                            }
                        }
                    }
                }

                // ENH-010: fill in a missing thesis/skripsi grade from the Excel value.
                $thesisGrade = trim((string) ($student['nilai_skripsi'] ?? ''));
                if ($thesisGrade !== '') {
                    $nimSafe   = trim((string) ($student['nim'] ?? $g['nim']));
                    $trResp    = $this->neoFeeder->getCekTranskripMahasiswa($apiToken, $nimSafe);
                    $thesisRow = ($trResp['error_code'] ?? -1) === 0
                        ? $this->findMissingGradeThesis($trResp['data'] ?? [])
                        : null;
                    if ($thesisRow !== null) {
                        $gradeResp = $this->neoFeeder->updateNilaiPerkuliahanKelas(
                            $apiToken,
                            (string) $thesisRow['id_reg_pd'],
                            (string) $thesisRow['id_kls'],
                            ['nilai_huruf' => $thesisGrade]
                        );
                        if (($gradeResp['error_code'] ?? -1) === 0) {
                            $results[] = ['nim' => $g['nim'], 'success' => true, 'msg' => 'Nilai skripsi terisi.'];
                        } else {
                            $results[] = ['nim' => $g['nim'], 'success' => false, 'msg' => $gradeResp['error_msg'] ?? 'Gagal mengisi nilai skripsi.'];
                        }
                    }
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

        $headers = ['nim', 'nama', 'tgl_keluar', 'ipk', 'nilai_skripsi'];
        $columns = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue($columns[$i] . '1', $header);
        }

        $example = ['12345678', 'Nama Mahasiswa (optional)', '2026-08-31', '3.75', 'A'];
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
                'tgl_keluar'     => isset($colIndex['tgl_keluar']) ? $this->normalizeTanggalKeluar($row[$colIndex['tgl_keluar']] ?? '') : '',
                'periode_keluar' => isset($colIndex['periode_keluar']) ? trim((string) ($row[$colIndex['periode_keluar']] ?? '')) : '',
                'ipk'            => isset($colIndex['ipk']) ? trim((string) ($row[$colIndex['ipk']] ?? '')) : '',
                'nilai_skripsi'  => isset($colIndex['nilai_skripsi']) ? strtoupper(trim((string) ($row[$colIndex['nilai_skripsi']] ?? ''))) : '',
                'saved'          => false,
            ];
        }

        return $students;
    }

    /**
     * Normalizes a tgl_keluar cell to a plain `YYYY-MM-DD` string regardless of how
     * PhpSpreadsheet surfaced it: a DateTime object, an Excel serial date number
     * (with setReadDataOnly(true) date cells arrive as serials such as 46207), or a
     * pre-formatted string. ENH-005.
     *
     * @param mixed $cell The raw spreadsheet cell value.
     */
    private function normalizeTanggalKeluar($cell): string
    {
        if ($cell instanceof \DateTimeInterface) {
            return $cell->format('Y-m-d');
        }

        if (is_int($cell) || (is_string($cell) && ctype_digit($cell))) {
            $serial = (int) $cell;
            // Excel serial dates for plausible graduation years (2000-01-01..2099-12-31).
            if ($serial >= 36526 && $serial <= 73049) {
                try {
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($serial)->format('Y-m-d');
                } catch (\Throwable $e) {
                    // fall through to string below
                }
            }
        }

        return trim((string) $cell);
    }
}
