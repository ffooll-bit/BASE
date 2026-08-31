<?php

namespace App\Controllers;

use App\Libraries\NeoFeeder;
use App\Libraries\VerifikasiIpkStore;

/**
 * Verifikasi IPK — bulk check and, where requested, correct the last-semester
 * IPK for many students against an Excel file (Graduation template: nim + ipk).
 *
 * Each student costs one synchronous getAktivitasKuliahMahasiswa call to read
 * the Neo Feeder IPK, so students are processed in small batches (BATCH_PER_PAGE)
 * to avoid PHP's max_execution_time on large files. The uploaded list lives in a
 * cache store + resume cookie separate from the graduation WizardProgress.
 */
class VerifikasiIpk extends BaseController
{
    private const BATCH_PER_PAGE = 25;

    private NeoFeeder $neoFeeder;
    private VerifikasiIpkStore $store;

    public function __construct()
    {
        $this->neoFeeder = service('neoFeeder');
        $this->store     = service('ipkVerif');
    }

    private function render(string $view, array $data): string
    {
        $username = $data['username'] ?? service('auth')->getCurrentUser() ?? '';
        $title    = $data['title'] ?? 'Verifikasi IPK';

        return view('layout/header', ['username' => $username, 'title' => $title])
            . view('layout/sidebar')
            . view($view, $data)
            . view('layout/footer');
    }

    private function loadState(): ?array
    {
        $token = $this->store->getResumeToken();
        if ($token === null) {
            return null;
        }

        $state = $this->store->load($token);

        return is_array($state) ? $state : null;
    }

    /**
     * GET /verifikasi-ipk — upload form (with optional resume prompt).
     */
    public function index()
    {
        $token     = $this->store->getResumeToken();
        $canResume = $token !== null && $this->store->load($token) !== null;

        return $this->render('verifikasi_ipk/index', [
            'username'  => service('auth')->getCurrentUser() ?? '',
            'canResume' => $canResume,
            'error'     => session()->getFlashdata('verifikasi_ipk_error'),
        ]);
    }

    /**
     * POST /verifikasi-ipk/upload — parse Excel (nim + ipk), seed the store.
     */
    public function upload()
    {
        $file = $this->request->getFile('excel');
        if ($file === null || ! $file->isValid()) {
            return redirect()->back()
                ->with('verifikasi_ipk_error', 'Unggah file Excel (.xlsx) terlebih dahulu.')
                ->withInput();
        }

        if (strtolower($file->getExtension()) !== 'xlsx') {
            return redirect()->back()
                ->with('verifikasi_ipk_error', 'File harus berformat .xlsx (Excel 2007+).')
                ->withInput();
        }

        try {
            $students = $this->parseExcel($file->getTempName());
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('verifikasi_ipk_error', 'Gagal membaca Excel: ' . $e->getMessage())
                ->withInput();
        }

        if (count($students) === 0) {
            return redirect()->back()
                ->with('verifikasi_ipk_error', 'Tidak ada baris valid di Excel (kolom "nim" wajib).')
                ->withInput();
        }

        $token = $this->store->generateToken();
        $state = ['students' => $students, 'current' => 0, 'results' => []];
        $this->store->save($token, $state);
        $this->store->setResumeCookie($token);

        return redirect()->to('verifikasi-ipk/verif');
    }

    /**
     * POST /verifikasi-ipk/cancel — clear an in-progress verification session.
     */
    public function cancel()
    {
        $token = $this->store->getResumeToken();
        if ($token !== null) {
            $this->store->clear($token);
            $this->store->clearResumeCookie();
        }

        return redirect()->to('verifikasi-ipk');
    }

    /**
     * GET /verifikasi-ipk/verif — render the current batch comparison.
     */
    public function verif()
    {
        $state = $this->loadState();
        if ($state === null) {
            return redirect()->to('verifikasi-ipk');
        }

        set_time_limit(0);

        $apiToken = session('auth.token');
        if ($apiToken === null) {
            return redirect()->to('verifikasi-ipk')
                ->with('verifikasi_ipk_error', 'Sesi token habis; silakan login ulang lalu lanjutkan.');
        }

        $activeSemesterIds = $this->activeSemesterIds($apiToken);
        $students  = $state['students'];
        $total     = count($students);
        $start     = $state['current'];
        $batch     = array_slice($students, $start, self::BATCH_PER_PAGE);
        $rows      = [];

        foreach ($batch as $idx => $s) {
            $rows[$idx] = $this->buildRow($apiToken, $s, $activeSemesterIds, $start + $idx);
        }

        $nextStart = $start + count($batch);

        return $this->render('verifikasi_ipk/verification', [
            'username'      => service('auth')->getCurrentUser() ?? '',
            'rows'          => $rows,
            'start'         => $start,
            'nextStart'     => $nextStart,
            'total'         => $total,
            'isLast'        => $nextStart >= $total,
            'results'       => $state['results'] ?? [],
            'processed'     => $state['current'],
        ]);
    }

    /**
     * POST /verifikasi-ipk/apply — apply the Excel IPK to the ticked students'
     * active last semester, then advance to the next batch (or finish).
     */
    public function apply()
    {
        $state = $this->loadState();
        if ($state === null) {
            return redirect()->to('verifikasi-ipk');
        }

        set_time_limit(0);

        $apiToken = session('auth.token');
        if ($apiToken === null) {
            return redirect()->to('verifikasi-ipk')
                ->with('verifikasi_ipk_error', 'Sesi token habis; silakan login ulang lalu lanjutkan.');
        }

        $checked = (array) $this->request->getPost('fix');
        $current = (int) $state['current'];
        $students = $state['students'];
        $results  = $state['results'] ?? [];
        $activeSemesterIds = $this->activeSemesterIds($apiToken);

        foreach ($students as $idx => $s) {
            if ($idx < $current || $idx >= $current + self::BATCH_PER_PAGE) {
                continue;
            }
            if (! in_array((string) $idx, $checked, true)) {
                continue;
            }

            $excelIpk = trim((string) ($s['ipk'] ?? ''));
            if ($excelIpk === '') {
                $results[] = ['nim' => $s['nim'], 'success' => false, 'msg' => 'IPK Excel kosong.'];
                continue;
            }

            $last = $this->lastActiveAcademic($apiToken, $s['nim'], $activeSemesterIds);
            if ($last === null) {
                $results[] = ['nim' => $s['nim'], 'success' => false, 'msg' => 'Semester terakhir tidak aktif / tidak ditemukan.'];
                continue;
            }

            $idPembiayaan = $this->pembiayaanMahasiswa($apiToken, (string) $s['nim']);
            if ($idPembiayaan === null) {
                $results[] = ['nim' => $s['nim'], 'success' => false, 'msg' => 'id_pembiayaan tidak ditemukan.'];
                continue;
            }

            $record = [];
            foreach ($last as $k => $v) {
                if (in_array($k, ['id_registrasi_mahasiswa', 'id_semester', 'active'], true)) {
                    continue;
                }
                $record[$k] = $v;
            }
            $record['id_pembiayaan'] = $idPembiayaan;
            $record['ipk']           = $excelIpk;

            $resp = $this->neoFeeder->updatePerkuliahanMahasiswa(
                $apiToken,
                (string) $last['id_registrasi_mahasiswa'],
                (string) $last['id_semester'],
                $record
            );

            if (($resp['error_code'] ?? -1) === 0) {
                $results[] = ['nim' => $s['nim'], 'success' => true, 'msg' => 'IPK ter-update.'];
            } else {
                $results[] = ['nim' => $s['nim'], 'success' => false, 'msg' => $resp['error_msg'] ?? 'Gagal update IPK.'];
            }
        }

        $state['results']  = $results;
        $state['current']  = $current + self::BATCH_PER_PAGE;
        $token = $this->store->getResumeToken();

        if ($state['current'] >= count($students)) {
            $this->store->clear((string) $token);
            $this->store->clearResumeCookie();

            session()->setFlashdata('verifikasi_ipk_results', $results);

            return redirect()->to('verifikasi-ipk/results');
        }

        $this->store->save((string) $token, $state);

        return redirect()->to('verifikasi-ipk/verif');
    }

    /**
     * GET /verifikasi-ipk/results — post-apply summary list of per-student results.
     */
    public function results()
    {
        $results = session()->getFlashdata('verifikasi_ipk_results') ?? [];

        return $this->render('verifikasi_ipk/results', [
            'username' => service('auth')->getCurrentUser() ?? '',
            'results'  => $results,
        ]);
    }

    /**
     * Builds the display row for one student: Neo Feeder last-semester IPK,
     * Excel IPK, cocok/beda status, target semester and editability.
     */
    private function buildRow(string $apiToken, array $s, array $activeSemesterIds, int $globalIdx): array
    {
        $nim       = (string) $s['nim'];
        $excelIpk  = trim((string) ($s['ipk'] ?? ''));
        $last      = $this->lastActiveAcademic($apiToken, $nim, $activeSemesterIds);

        $row = [
            'idx'            => (string) $globalIdx,
            'nim'            => $nim,
            'nama'           => (string) ($s['nama'] ?? ''),
            'excelIpk'       => $excelIpk,
            'neoIpk'         => '',
            'semester'       => '',
            'active'         => false,
            'editable'       => false,
            'status'         => 'tidak_ditemukan',
        ];

        if ($last === null) {
            return $row;
        }

        $neoIpk = (string) ($last['ipk'] ?? '');
        $match  = $excelIpk !== ''
            && abs((float) $neoIpk - (float) $excelIpk) < 0.0001;

        $row['neoIpk']  = $neoIpk;
        $row['semester'] = (string) $last['id_semester'];
        $row['active']   = true;
        $row['editable'] = $last['active'];
        $row['status']   = $last['active'] ? ($match ? 'cocok' : 'beda') : 'non_aktif';

        return $row;
    }

    /**
     * Resolves the last academic row (largest id_semester) for a student and
     * whether that semester is currently active in Daftar Semester.
     *
     * @return array|null With keys id_semester, id_registrasi_mahasiswa, ipk, active.
     */
    private function lastActiveAcademic(string $apiToken, string $nim, array $activeSemesterIds): ?array
    {
        $nimSafe = str_replace("'", "\'", $nim);
        $resp = $this->neoFeeder->getAktivitasKuliahMahasiswa($apiToken, [
            'filter' => "nim='{$nimSafe}'",
            'order'  => 'id_semester asc',
            'limit'  => 50,
        ]);

        if (($resp['error_code'] ?? -1) !== 0 || empty($resp['data'])) {
            return null;
        }

        $rows = $resp['data'];
        usort($rows, fn ($a, $b) => strcmp($a['id_semester'] ?? '', $b['id_semester'] ?? ''));
        $last = $rows[count($rows) - 1];

        $last['active'] = in_array((string) ($last['id_semester'] ?? ''), $activeSemesterIds, true);

        return $last;
    }

    /**
     * Resolves id_pembiayaan for a student from Riwayat Pendidikan Mahasiswa.
     *
     * @return string|null The id_pembiayaan value, or null if not resolvable.
     */
    private function pembiayaanMahasiswa(string $apiToken, string $nim): ?string
    {
        $nimSafe  = str_replace("'", "\'", $nim);
        $resp     = $this->neoFeeder->getListRiwayatPendidikanMahasiswa($apiToken, [
            'filter' => "nim='{$nimSafe}'",
            'limit'  => 10,
        ]);
        $data = $resp['data'] ?? [];

        if (($resp['error_code'] ?? -1) !== 0 || ! is_array($data) || count($data) === 0) {
            return null;
        }

        $value = $data[0]['id_pembiayaan'] ?? null;

        return $value === null ? null : (string) $value;
    }

    /**
     * The set of semesters that are currently active (a_periode_aktif truthy).
     *
     * @return list<string>
     */
    private function activeSemesterIds(string $apiToken): array
    {
        $active = [];
        $resp   = $this->neoFeeder->getSemester($apiToken);

        if (($resp['error_code'] ?? -1) === 0 && isset($resp['data'])) {
            foreach ($resp['data'] as $sm) {
                if (! empty($sm['a_periode_aktif'])) {
                    $active[] = (string) $sm['id_semester'];
                }
            }
        }

        return $active;
    }

    /**
     * Parses the uploaded Excel file into a list of student records (nim + ipk,
     * nama optional for display). Header row matched case-insensitively.
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

        $header   = array_map(static fn ($c) => strtolower(trim((string) $c)), array_shift($rows));
        $colIndex = [];
        foreach (['nim' => 'nim', 'nama' => 'nama', 'ipk' => 'ipk'] as $key => $label) {
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
                'nim'  => $nim,
                'nama' => isset($colIndex['nama']) ? trim((string) ($row[$colIndex['nama']] ?? '')) : '',
                'ipk'  => isset($colIndex['ipk']) ? trim((string) ($row[$colIndex['ipk']] ?? '')) : '',
            ];
        }

        return $students;
    }
}
