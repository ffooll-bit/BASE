<?php

namespace App\Controllers;

class Mahasiswa extends BaseController
{
    private const ALLOWED_FILTERS = ['nim', 'nipd', 'nama_mahasiswa', 'angkatan'];
    private const PAGE_SIZE = 20;

    public function index()
    {
        $username = service('auth')->getCurrentUser();
        $rows = null;
        $error = null;
        $total = null;
        $filters = $this->collectFilters(self::ALLOWED_FILTERS);
        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = $this->resolvePerPage();
        $filterSql = $this->buildFilterSql(self::ALLOWED_FILTERS, $filters);

        $options = [];
        if ($filterSql !== '') {
            $options['filter'] = $filterSql;
        }
        $options['limit'] = $perPage;
        $options['offset'] = ($page - 1) * $perPage;

        $token = session('auth.token');
        if ($token !== null) {
            $response = service('neoFeeder')->getListMahasiswa($token, $options);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'])) {
                $rows = $response['data'];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data mahasiswa.';
            }

            $countOptions = $filterSql !== '' ? ['filter' => $filterSql] : [];
            $total = $this->parseCount(service('neoFeeder')->getCountMahasiswa($token, $countOptions));
        }

        $totalPages = $total !== null ? max(1, (int) ceil($total / $perPage)) : null;

        return view('layout/header', ['username' => $username, 'title' => 'Daftar Mahasiswa'])
            . view('layout/sidebar', ['username' => $username])
            . view('mahasiswa/index', [
                'username'   => $username,
                'rows'       => $rows,
                'error'      => $error,
                'filters'    => $filters,
                'page'       => $page,
                'pageSize'   => $perPage,
                'total'      => $total,
                'totalPages' => $totalPages,
                'labels'     => $this->columnLabels(),
            ])
            . view('layout/footer');
    }

    /**
     * Human-readable column labels for the Mahasiswa list.
     *
     * @return array<string, string>
     */
    protected function columnLabels(): array
    {
        return [
            'nama_mahasiswa'          => 'Nama Mahasiswa',
            'jenis_kelamin'           => 'Jenis Kelamin',
            'tanggal_lahir'           => 'Tanggal Lahir',
            'id_perguruan_tinggi'     => 'ID Perguruan Tinggi',
            'nipd'                    => 'NIPD',
            'ipk'                     => 'IPK',
            'total_sks'               => 'Total SKS',
            'id_sms'                  => 'ID SMS',
            'id_mahasiswa'            => 'ID Mahasiswa',
            'id_agama'                => 'ID Agama',
            'nama_agama'              => 'Agama',
            'id_prodi'                => 'ID Prodi',
            'nama_program_studi'      => 'Program Studi',
            'id_status_mahasiswa'     => 'ID Status',
            'nama_status_mahasiswa'   => 'Status Mahasiswa',
            'nim'                     => 'NIM',
            'id_periode'              => 'ID Periode',
            'nama_periode_masuk'      => 'Periode Masuk',
            'id_registrasi_mahasiswa' => 'ID Registrasi',
            'id_periode_keluar'       => 'ID Periode Keluar',
            'tanggal_keluar'          => 'Tanggal Keluar',
            'last_update'             => 'Last Update',
            'tgl_create'              => 'Tanggal Dibuat',
            'status_sync'             => 'Status Sync',
        ];
    }

    public function edit($id = null)
    {
        $username = service('auth')->getCurrentUser();
        $row = null;
        $error = null;
        if ($id !== null) {
            ['row' => $row, 'error' => $error] = $this->fetchBiodata((string) $id);
        }

        return view('layout/header', ['username' => $username, 'title' => 'Edit Mahasiswa'])
            . view('layout/sidebar', ['username' => $username])
            . view('mahasiswa/edit', [
                'username' => $username,
                'row'      => $row,
                'error'    => $error,
                'id'       => $id,
            ])
            . view('layout/footer');
    }

    /**
     * GET /mahasiswa/detail/(:any) — read-only single-student detail (ENH-021).
     */
    public function detail($id = null)
    {
        $username = service('auth')->getCurrentUser();
        $row = null;
        $error = null;
        if ($id !== null) {
            ['row' => $row, 'error' => $error] = $this->fetchBiodata((string) $id);
        }

        return view('layout/header', ['username' => $username, 'title' => 'Detail Mahasiswa'])
            . view('layout/sidebar', ['username' => $username])
            . view('mahasiswa/detail', [
                'username' => $username,
                'row'      => $row,
                'error'    => $error,
                'id'       => $id,
            ])
            . view('layout/footer');
    }

    /**
     * Fetches a single student's full biodata by id_mahasiswa via GetBiodataMahasiswa.
     *
     * @return array{row:mixed|null, error:string|null}
     */
    private function fetchBiodata(string $id): array
    {
        $token = session('auth.token');
        $row = null;
        $error = null;

        if ($token !== null) {
            $idSafe = str_replace("'", "\'", $id);
            $response = service('neoFeeder')->getBiodataMahasiswa($token, [
                'filter' => "id_mahasiswa='{$idSafe}'",
                'limit'  => 1,
            ]);
            if (($response['error_code'] ?? -1) === 0 && !empty($response['data'])) {
                $row = $response['data'][0];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data mahasiswa.';
            }
        }

        return ['row' => $row, 'error' => $error];
    }

    public function editPost($id = null)
    {
        $token = session('auth.token');
        $record = $this->request->getPost();
        unset($record[csrf_token()], $record['id_mahasiswa']);

        $response = service('neoFeeder')->updateBiodataMahasiswa($token, (string) $id, $record);
        if (($response['error_code'] ?? -1) === 0) {
            return redirect()->to('mahasiswa')->with('message', 'Data mahasiswa berhasil diperbarui.');
        }

        return redirect()->back()->with('error', $response['error_msg'] ?? 'Gagal memperbarui data.')->withInput();
    }

    public function delete($id = null)
    {
        $token = session('auth.token');
        $response = service('neoFeeder')->deleteBiodataMahasiswa($token, (string) $id);
        if (($response['error_code'] ?? -1) === 0) {
            return redirect()->to('mahasiswa')->with('message', 'Data mahasiswa berhasil dihapus.');
        }

        return redirect()->back()->with('error', $response['error_msg'] ?? 'Gagal menghapus data.')->withInput();
    }
}
