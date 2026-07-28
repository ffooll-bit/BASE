<?php

namespace App\Controllers;

class ProfilPT extends BaseController
{
    public function index()
    {
        $username = service('auth')->getCurrentUser();
        $profilPT = null;
        $error = null;

        $token = session('auth.token');
        if ($token !== null) {
            $response = service('neoFeeder')->getProfilPT($token);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'][0])) {
                $profilPT = $response['data'][0];
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data profil perguruan tinggi.';
            }
        }

        return view('layout/header', ['username' => $username, 'title' => 'Profil Perguruan Tinggi'])
            . view('layout/sidebar', ['username' => $username])
            . view('profil_pt/index', compact('username', 'profilPT', 'error'))
            . view('layout/footer');
    }
}
