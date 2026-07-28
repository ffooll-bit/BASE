<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $username = service('auth')->getCurrentUser();

        return view('layout/header', ['username' => $username, 'title' => 'Dashboard'])
            . view('layout/sidebar', ['username' => $username])
            . view('dashboard/index', ['username' => $username])
            . view('layout/footer');
    }
}
