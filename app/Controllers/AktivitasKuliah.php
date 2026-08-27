<?php

namespace App\Controllers;

class AktivitasKuliah extends BaseController
{
    private const ALLOWED_FILTERS = ['nim', 'nama_mahasiswa', 'angkatan', 'id_semester'];
    private const PAGE_SIZE = 20;

    public function index()
    {
        $username = service('auth')->getCurrentUser();
        $rows = null;
        $error = null;
        $filters = $this->collectFilters(self::ALLOWED_FILTERS);
        $page = max(1, (int) $this->request->getGet('page'));
        $options = $filters;
        $options['limit'] = self::PAGE_SIZE;
        $options['offset'] = ($page - 1) * self::PAGE_SIZE;

        $token = session('auth.token');
        if ($token !== null) {
            $response = service('neoFeeder')->getAktivitasKuliahMahasiswa($token, $options);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'])) {
                $rows = $response['data'];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data aktivitas kuliah mahasiswa.';
            }
        }

        return view('layout/header', ['username' => $username, 'title' => 'Aktivitas Kuliah Mahasiswa'])
            . view('layout/sidebar', ['username' => $username])
            . view('aktivitas_kuliah/index', [
                'username' => $username,
                'rows'     => $rows,
                'error'    => $error,
                'filters'  => $filters,
                'page'     => $page,
                'pageSize' => self::PAGE_SIZE,
            ])
            . view('layout/footer');
    }
}
