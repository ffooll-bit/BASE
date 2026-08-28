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
        $filters = $this->collectFilters(self::ALLOWED_FILTERS);
        $page = max(1, (int) $this->request->getGet('page'));
        $options = $filters;
        $options['limit'] = self::PAGE_SIZE;
        $options['offset'] = ($page - 1) * self::PAGE_SIZE;

        $token = session('auth.token');
        if ($token !== null) {
            $response = service('neoFeeder')->getListMahasiswa($token, $options);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'])) {
                $rows = $response['data'];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data mahasiswa.';
            }
        }

        return view('layout/header', ['username' => $username, 'title' => 'Daftar Mahasiswa'])
            . view('layout/sidebar', ['username' => $username])
            . view('mahasiswa/index', [
                'username' => $username,
                'rows'     => $rows,
                'error'    => $error,
                'filters'  => $filters,
                'page'     => $page,
                'pageSize' => self::PAGE_SIZE,
            ])
            . view('layout/footer');
    }

    public function edit($id = null)
    {
        $username = service('auth')->getCurrentUser();
        $row = null;
        $token = session('auth.token');
        $error = null;

        if ($token !== null && $id !== null) {
            $idSafe = str_replace("'", "\'", (string) $id);
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
