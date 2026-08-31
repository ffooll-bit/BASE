<?php

namespace App\Libraries;

use CodeIgniter\Cache\CacheInterface;

/**
 * Cache-backed store + resume cookie for the "Verifikasi IPK" (bulk last-semester
 * IPK check) flow. Deliberately separate from WizardProgress so the two flows
 * never share a store and cannot clear each other's progress.
 */
class VerifikasiIpkStore
{
    private const KEY_PREFIX = 'ipk_verif_';
    private const COOKIE_NAME = 'ipk_verif_resume';
    private const DEFAULT_TTL = 86400;

    private CacheInterface $cache;
    private int $ttl;

    public function __construct(CacheInterface $cache, int $ttl = self::DEFAULT_TTL)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * Generates an opaque resume token for a verification session.
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Persists the verification state, independent of the auth session.
     *
     * @param string $token The resume token.
     * @param array  $state The state to store.
     */
    public function save(string $token, array $state): bool
    {
        return $this->cache->save(self::KEY_PREFIX . $token, $state, $this->ttl);
    }

    /**
     * Loads the verification state, or null if none/expired.
     *
     * @return array|null The stored state, or null.
     */
    public function load(string $token): ?array
    {
        $state = $this->cache->get(self::KEY_PREFIX . $token);

        return is_array($state) ? $state : null;
    }

    /**
     * Removes persisted verification state.
     */
    public function clear(string $token): void
    {
        $this->cache->delete(self::KEY_PREFIX . $token);
    }

    public function setResumeCookie(string $token): void
    {
        // ponytail: opaque random key, not sensitive; mirror of Auth::setWizardResumeCookie
        setcookie(self::COOKIE_NAME, $token, [
            'expires'  => time() + $this->ttl,
            'path'     => '/',
            'secure'   => ENVIRONMENT === 'production',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Reads the resume token from the request cookie.
     */
    public function getResumeToken(): ?string
    {
        return $_COOKIE[self::COOKIE_NAME] ?? null;
    }

    public function clearResumeCookie(): void
    {
        setcookie(self::COOKIE_NAME, '', [
            'expires'  => 1,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
