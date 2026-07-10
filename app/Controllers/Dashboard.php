<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): void
    {
        $username = service('auth')->getCurrentUser();

        echo view('layout/header');
        echo view('layout/sidebar');
        echo view('dashboard/index', ['username' => $username]);
        echo view('layout/footer');
    }
}
