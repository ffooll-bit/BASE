<?php

namespace App\Controllers;

class ProfilPT extends BaseController
{
    public function index()
    {
        $username = service('auth')->getCurrentUser();
        $profilPT = null;
        $error = null;
        $tanggalSK = '-';

        $token = session('auth.token');
        if ($token !== null) {
            $response = service('neoFeeder')->getProfilPT($token);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'][0])) {
                $profilPT = $response['data'][0];
                $tanggalSK = isset($profilPT['tanggal_sk_pendirian'])
                    ? date('d F Y', strtotime($profilPT['tanggal_sk_pendirian']))
                    : '-';
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data profil perguruan tinggi.';
            }
        }

        return view('layout/header', ['username' => $username, 'title' => 'Profil Perguruan Tinggi'])
            . view('layout/sidebar', ['username' => $username])
            . view('profil_pt/index', compact('username', 'profilPT', 'error', 'tanggalSK'))
            . view('layout/footer');
    }
}
