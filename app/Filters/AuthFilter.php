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
     * The login route whitelist is handled by CI4's `except` config
     * in Filters.php (TASK-013). This filter only protects non-login
     * routes. The prior-auth cookie detects session expiry and shows
     * a flashdata message.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');

        if ($auth->isLoggedIn()) {
            return null;
        }

        // Check prior-auth cookie for session-expiry hint
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
