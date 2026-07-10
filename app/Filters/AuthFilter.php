<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Runs before every request to enforce authentication.
     *
     * Whitelisted routes (login) allow unauthenticated access but redirect
     * authenticated users to /dashboard. All other routes require a valid
     * session; the prior-auth cookie detects session expiry and shows a
     * message.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth   = service('auth');
        $path   = $request->uri->getPath();
        $isLogin = $path === 'login';

        // Whitelisted routes: /login (GET and POST)
        if ($isLogin) {
            if ($auth->isLoggedIn()) {
                return redirect()->to('/dashboard');
            }
            return null;
        }

        // Protected routes
        if ($auth->isLoggedIn()) {
            return null;
        }

        // Not logged in — check prior-auth cookie for session-expiry hint
        if ($auth->hasPriorAuthCookie()) {
            $auth->clearPriorAuthCookie();
            session()->setFlashdata('message', 'Your session has expired. Please log in again.');
        }

        return redirect()->to('/login');
    }

    /**
     * Runs after every request.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ponytail: empty after-filter required by FilterInterface
    }
}
