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
        $website = '-';

        $bulan = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember',
        ];

        $token = session('auth.token');
        if ($token !== null) {
            $response = service('neoFeeder')->getProfilPT($token);
            if (($response['error_code'] ?? -1) === 0 && isset($response['data'][0])) {
                $profilPT = $response['data'][0];
                $tanggalSK = isset($profilPT['tanggal_sk_pendirian'])
                    ? strtr(date('d F Y', strtotime($profilPT['tanggal_sk_pendirian'])), $bulan)
                    : '-';
                $rawUrl = $profilPT['website'] ?? '';
                if ($rawUrl === '') {
                    $website = '-';
                } else {
                    $website = preg_match('#^https?://#i', $rawUrl)
                        ? $rawUrl
                        : 'https://' . $rawUrl;
                }
            } else {
                $error = $response['error_msg'] ?? 'Gagal memuat data profil perguruan tinggi.';
            }
        }

        return view('layout/header', ['username' => $username, 'title' => 'Profil Perguruan Tinggi'])
            . view('layout/sidebar', ['username' => $username])
            . view('profil_pt/index', compact('username', 'profilPT', 'error', 'tanggalSK', 'website'))
            . view('layout/footer');
    }
}
