<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Home extends BaseController
{
    public function index(): RedirectResponse
    {
        if (service('auth')->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login');
    }
}
