<?php

namespace App\Libraries;

use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Session\Session;

class Auth
{
    /**
     * The Neo Feeder API service instance.
     *
     * @var NeoFeeder
     */
    private NeoFeeder $neoFeeder;

    /**
     * The CI4 Session instance.
     *
     * @var Session
     */
    private Session $session;

    /**
     * The CI4 Encryption service instance.
     *
     * @var EncrypterInterface
     */
    private EncrypterInterface $encryption;

    /**
     * The last error message string, or null if no error.
     *
     * @var string|null
     */
    private ?string $lastError = null;

    /**
     * Constructor.
     *
     * @param NeoFeeder          $neoFeeder  The Neo Feeder API service.
     * @param Session            $session    The CI4 Session service.
     * @param EncrypterInterface $encryption The CI4 Encryption service.
     */
    public function __construct(NeoFeeder $neoFeeder, Session $session, EncrypterInterface $encryption)
    {
        $this->neoFeeder  = $neoFeeder;
        $this->session    = $session;
        $this->encryption = $encryption;
    }

    /**
     * Returns the last error message string, or null if no error has occurred.
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Reads the token validation TTL (time-to-live) from the Neo Feeder
     * configuration.
     *
     * @return int The validation TTL in seconds.
     */
    private function getValidationTTL(): int
    {
        return config('NeoFeeder')->validationTTL;
    }

    /**
     * Attempts to authenticate a user with the given username and password.
     *
     * Validates inputs, calls the Neo Feeder API, and on success stores
     * the session and sets a persistent prior-auth cookie. The password is
     * never stored, logged, or retained beyond the single API call.
     *
     * @param string $username The user's username or email.
     * @param string $password The user's password (never stored or logged).
     *
     * @return bool True if authentication succeeds, false on failure.
     */
    public function login(string $username, string $password): bool
    {
        if ($username === '' || $password === '') {
            $this->lastError = 'Please enter your username and password.';

            return false;
        }

        $response = $this->neoFeeder->getToken($username, $password);

        // Success: error_code === 0 with a non-null data.token
        if ($response['error_code'] === 0 && isset($response['data']['token'])) {
            $token = $response['data']['token'];

            $this->session->set('auth', [
                'token'           => $token,
                'username'        => $username,
                'lastValidatedAt' => time(),
            ]);

            $this->session->regenerate();
            $this->setPriorAuthCookie($username, $token);

            return true;
        }

        // Connection / timeout failure
        if ($response['error_code'] === -1) {
            $this->lastError = 'Unable to connect to the authentication server. Please try again later.';

            return false;
        }

        // All other failures (invalid credentials, malformed response, API error, etc.)
        $this->lastError = 'Login failed. Please check your credentials.';

        return false;
    }

    /**
     * Destroys the current session and clears the persistent prior-auth cookie.
     *
     * @return void
     */
    public function logout(): void
    {
        $this->session->destroy();
        $this->clearPriorAuthCookie();
    }

    /**
     * Encrypts and signs "username|token_hash" using the CI4 Encryption service
     * and stores it in an HTTP-only, SameSite=Lax cookie with a 24-hour lifetime.
     *
     * @param string $username The authenticated username.
     * @param string $token    The authentication token.
     *
     * @return void
     */
    private function setPriorAuthCookie(string $username, string $token): void
    {
        $value = $this->encryption->encrypt($username . '|' . hash('sha256', $token));

        // ponytail: setcookie with array-options form avoids cookie-helper dependency
        setcookie('prior_auth', base64_encode($value), [
            'expires'  => time() + 86400, // 24 hours
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Deletes the prior-auth cookie by setting its expiration to the past.
     *
     * @return void
     */
    private function clearPriorAuthCookie(): void
    {
        setcookie('prior_auth', '', [
            'expires'  => 1,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Checks whether the prior-auth cookie is present in the current request.
     *
     * @return bool True if the cookie exists.
     */
    private function hasPriorAuthCookie(): bool
    {
        return isset($_COOKIE['prior_auth']);
    }

    /**
     * Checks whether the user is currently logged in.
     *
     * Returns true only if a session token exists and has been validated
     * within the TTL window, or if a fresh validation succeeds.
     *
     * @return bool True if the user is authenticated.
     */
    public function isLoggedIn(): bool
    {
        $token = $this->session->get('auth.token');

        if ($token === null) {
            return false;
        }

        $lastValidatedAt = $this->session->get('auth.lastValidatedAt');

        if ($lastValidatedAt !== null && (time() - $lastValidatedAt) < $this->getValidationTTL()) {
            return true;
        }

        return $this->validateToken();
    }

    /**
     * Returns the currently authenticated username from the session, or null.
     *
     * @return string|null The username, or null if not authenticated.
     */
    public function getCurrentUser(): ?string
    {
        return $this->session->get('auth.username');
    }

    /**
     * Validates the current session token against the Neo Feeder API.
     *
     * On success, refreshes the validation cache. On invalid token or
     * malformed response, clears the session and records the error.
     * On connection failure, falls back to cached validation within TTL.
     *
     * @return bool True if the token is valid.
     */
    public function validateToken(): bool
    {
        $response   = $this->neoFeeder->getProfilPT($this->session->get('auth.token'));
        $errorCode  = $response['error_code'] ?? null;
        $lastValidatedAt = $this->session->get('auth.lastValidatedAt');
        $ttl = $this->getValidationTTL();

        if ($errorCode === 0) {
            $this->session->set('auth.lastValidatedAt', time());
            return true;
        }

        if ($errorCode === 100) {
            $this->session->remove('auth');
            $this->lastError = 'session expired';
            return false;
        }

        if ($errorCode === -1) {
            if ($lastValidatedAt !== null && (time() - $lastValidatedAt) < $ttl) {
                $this->session->set('auth.lastValidatedAt', time());
                return true;
            }
            return false;
        }

        if ($errorCode === -2) {
            $this->session->remove('auth');
            $this->lastError = 'session expired';
            return false;
        }

        // Other error codes (1, 99, etc.) — deny access, keep session intact.
        return false;
    }
}
