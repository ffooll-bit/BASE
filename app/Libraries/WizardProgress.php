<?php

namespace App\Libraries;

use CodeIgniter\Cache\CacheInterface;

class WizardProgress
{
    private const KEY_PREFIX = 'wizard_progress_';
    private const DEFAULT_TTL = 86400;

    private CacheInterface $cache;
    private int $ttl;

    public function __construct(CacheInterface $cache, int $ttl = self::DEFAULT_TTL)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    /**
     * Generates an opaque resume token for a wizard session.
     *
     * @return string The generated token (32 hex chars).
     */
    public function generateToken(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Persists wizard progress, independent of the auth session.
     *
     * @param string $token The resume token.
     * @param array  $state The progress state to store.
     *
     * @return bool True on success, false on failure.
     */
    public function save(string $token, array $state): bool
    {
        return $this->cache->save(self::KEY_PREFIX . $token, $state, $this->ttl);
    }

    /**
     * Loads wizard progress, or null if none/expired.
     *
     * @param string $token The resume token.
     *
     * @return array|null The stored state, or null.
     */
    public function load(string $token): ?array
    {
        $state = $this->cache->get(self::KEY_PREFIX . $token);

        return is_array($state) ? $state : null;
    }

    /**
     * Removes persisted wizard progress.
     *
     * @param string $token The resume token.
     *
     * @return void
     */
    public function clear(string $token): void
    {
        $this->cache->delete(self::KEY_PREFIX . $token);
    }
}
