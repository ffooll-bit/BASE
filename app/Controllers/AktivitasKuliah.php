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

    public function edit($idRegistrasi = null, $idSemester = null)
    {
        $username = service('auth')->getCurrentUser();
        $row = null;
        $error = null;
        $token = session('auth.token');

        if ($token !== null && $idRegistrasi !== null && $idSemester !== null) {
            $response = service('neoFeeder')->getAktivitasKuliahMahasiswa($token, [
                'filter' => ['id_registrasi_mahasiswa' => $idRegistrasi, 'id_semester' => $idSemester],
                'limit'  => 1,
            ]);
            if (($response['error_code'] ?? -1) === 0 && !empty($response['data'])) {
                $row = $response['data'][0];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data aktivitas kuliah mahasiswa.';
            }
        }

        return view('layout/header', ['username' => $username, 'title' => 'Edit Aktivitas Kuliah'])
            . view('layout/sidebar', ['username' => $username])
            . view('aktivitas_kuliah/edit', [
                'username'      => $username,
                'row'           => $row,
                'error'         => $error,
                'idRegistrasi'  => $idRegistrasi,
                'idSemester'    => $idSemester,
            ])
            . view('layout/footer');
    }

    public function editPost($idRegistrasi = null, $idSemester = null)
    {
        $token = session('auth.token');
        $record = $this->request->getPost();
        unset($record[csrf_token()], $record['id_registrasi_mahasiswa'], $record['id_semester']);

        $response = service('neoFeeder')->updatePerkuliahanMahasiswa($token, (string) $idRegistrasi, (string) $idSemester, $record);
        if (($response['error_code'] ?? -1) === 0) {
            return redirect()->to('aktivitas-kuliah')->with('message', 'Data aktivitas kuliah berhasil diperbarui.');
        }

        return redirect()->back()->with('error', $response['error_msg'] ?? 'Gagal memperbarui data.')->withInput();
    }

    public function delete($idRegistrasi = null, $idSemester = null)
    {
        $token = session('auth.token');
        $response = service('neoFeeder')->deletePerkuliahanMahasiswa($token, (string) $idRegistrasi, (string) $idSemester);
        if (($response['error_code'] ?? -1) === 0) {
            return redirect()->to('aktivitas-kuliah')->with('message', 'Data aktivitas kuliah berhasil dihapus.');
        }

        return redirect()->back()->with('error', $response['error_msg'] ?? 'Gagal menghapus data.')->withInput();
    }
}
