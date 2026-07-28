<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $username = service('auth')->getCurrentUser();

        return view('layout/header', ['username' => $username])
            . view('layout/sidebar')
            . view('dashboard/index', ['username' => $username])
            . view('layout/footer');
    }
}
