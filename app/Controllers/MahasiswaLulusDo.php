<?php

namespace App\Controllers;

class MahasiswaLulusDo extends BaseController
{
    private const ALLOWED_FILTERS = ['nim', 'nama_mahasiswa', 'id_jenis_keluar'];
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
            $response = service('neoFeeder')->getListMahasiswaLulusDO($token, $options);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'])) {
                $rows = $response['data'];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data mahasiswa lulus/dropout.';
            }

            $countOptions = $filterSql !== '' ? ['filter' => $filterSql] : [];
            $total = $this->parseCount(service('neoFeeder')->getCountMahasiswaLulusDO($token, $countOptions));
        }

        $totalPages = $total !== null ? max(1, (int) ceil($total / $perPage)) : null;

        return view('layout/header', ['username' => $username, 'title' => 'Daftar Mahasiswa Lulus / Dropout'])
            . view('layout/sidebar', ['username' => $username])
            . view('mahasiswa_lulus_do/index', [
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
     * Human-readable column labels for the Mahasiswa Lulus/DO list.
     *
     * @return array<string, string>
     */
    protected function columnLabels(): array
    {
        return [
            'id_registrasi_mahasiswa' => 'ID Registrasi',
            'id_mahasiswa'            => 'ID Mahasiswa',
            'id_perguruan_tinggi'     => 'ID Perguruan Tinggi',
            'id_prodi'                => 'ID Prodi',
            'tgl_masuk_sp'            => 'Tanggal Masuk SP',
            'tgl_keluar'              => 'Tanggal Keluar',
            'skhun'                   => 'SKHUN',
            'no_peserta_ujian'        => 'No Peserta Ujian',
            'no_seri_ijazah'          => 'No Seri Ijazah',
            'tgl_create'              => 'Tanggal Dibuat',
            'sks_diakui'              => 'SKS Diakui',
            'jalur_skripsi'           => 'Jalur Skripsi',
            'judul_skripsi'           => 'Judul Skripsi',
            'bln_awal_bimbingan'      => 'Bulan Awal Bimbingan',
            'bln_akhir_bimbingan'     => 'Bulan Akhir Bimbingan',
            'sk_yudisium'             => 'SK Yudisium',
            'tgl_sk_yudisium'         => 'Tanggal SK Yudisium',
            'ipk'                     => 'IPK',
            'sert_prof'               => 'Sertifikat Profesi',
            'a_pindah_mhs_asing'      => 'Pindah Mahasiswa Asing',
            'id_pt_asal'              => 'ID PT Asal',
            'id_prodi_asal'           => 'ID Prodi Asal',
            'nm_pt_asal'              => 'Nama PT Asal',
            'nm_prodi_asal'           => 'Nama Prodi Asal',
            'id_jns_daftar'           => 'ID Jenis Daftar',
            'id_jns_keluar'           => 'ID Jenis Keluar',
            'id_jalur_masuk'          => 'ID Jalur Masuk',
            'id_pembiayaan'           => 'ID Pembiayaan',
            'id_minat_bidang'         => 'ID Minat Bidang',
            'bidang_mayor'            => 'Bidang Mayor',
            'bidang_minor'            => 'Bidang Minor',
            'biaya_masuk_kuliah'      => 'Biaya Masuk Kuliah',
            'namapt'                  => 'Nama PT',
            'id_jur'                  => 'ID Jurusan',
            'nm_jns_daftar'           => 'Nama Jenis Daftar',
            'nm_smt'                  => 'Nama Semester',
            'nim'                     => 'NIM',
            'nama_mahasiswa'          => 'Nama Mahasiswa',
            'nama_program_studi'      => 'Program Studi',
            'angkatan'                => 'Angkatan',
            'id_jenis_keluar'         => 'ID Jenis Keluar',
            'nama_jenis_keluar'       => 'Jenis Keluar',
            'tanggal_keluar'          => 'Tanggal Keluar',
            'id_periode_keluar'       => 'ID Periode Keluar',
            'keterangan'              => 'Keterangan',
            'no_sertifikat_profesi'   => 'No Sertifikat Profesi',
            'tanggal_terbit_ijazah'   => 'Tanggal Terbit Ijazah',
            'status_sync'             => 'Status Sync',
        ];
    }
}
