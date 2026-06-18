<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class NeoFeeder extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Neo Feeder API Base URL
     * --------------------------------------------------------------------------
     *
     * The base URL for the Neo Feeder Web Service API endpoint.
     * All API requests (GetToken, GetProfilPT, etc.) are sent to this URL.
     *
     * Override via .env: neofeeder.apiBaseUrl
     */
    public string $apiBaseUrl = 'https://neofeeder.example.com/ws/live2.php';

    /**
     * --------------------------------------------------------------------------
     * Connection Timeout (seconds)
     * --------------------------------------------------------------------------
     *
     * The maximum number of seconds to wait for a connection to the Neo Feeder
     * API server before timing out.
     *
     * Override via .env: neofeeder.connectionTimeout
     */
    public int $connectionTimeout = 10;

    /**
     * --------------------------------------------------------------------------
     * Request Timeout (seconds)
     * --------------------------------------------------------------------------
     *
     * The maximum number of seconds to wait for the Neo Feeder API to complete
     * a request (including connection) before timing out.
     *
     * Override via .env: neofeeder.requestTimeout
     */
    public int $requestTimeout = 30;

    /**
     * --------------------------------------------------------------------------
     * Validation TTL (seconds)
     * --------------------------------------------------------------------------
     *
     * The time-to-live for cached token validation results. After this many
     * seconds since the last successful validation, the token will be
     * re-validated against the Neo Feeder API.
     *
     * Default: 300 seconds (5 minutes).
     * Override via .env: neofeeder.validationTTL
     */
    public int $validationTTL = 300;
}
