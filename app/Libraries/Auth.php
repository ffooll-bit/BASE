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
}
