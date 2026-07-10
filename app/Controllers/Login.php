<?php

namespace App\Controllers;

class Login extends BaseController
{
    /**
     * Show the login page, or redirect to dashboard if already authenticated.
     */
    public function index()
    {
        if (service('auth')->isLoggedIn()) {
            return redirect()->to('/dashboard');
        }

        return view('login/login');
    }

    /**
     * Attempt to log in with the posted credentials.
     */
    public function attemptLogin()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        if (service('auth')->login($username, $password)) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login')->with('error', service('auth')->getLastError());
    }

    /**
     * Log the user out and redirect to the login page.
     */
    public function logout()
    {
        service('auth')->logout();

        return redirect()->to('/login');
    }
}
