<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use Config\NeoFeeder as NeoFeederConfig;

class NeoFeeder
{
    /**
     * The Neo Feeder configuration instance.
     *
     * @var NeoFeederConfig
     */
    private NeoFeederConfig $config;

    /**
     * The CI4 HTTP client instance.
     *
     * @var CURLRequest
     */
    private CURLRequest $client;

    /**
     * Constructor.
     *
     * @param NeoFeederConfig $config The Neo Feeder configuration (API base URL, timeouts).
     * @param CURLRequest     $client The CI4 HTTP client for sending requests.
     */
    public function __construct(NeoFeederConfig $config, CURLRequest $client)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Builds and executes an HTTP POST request to the Neo Feeder API.
     *
     * On success, returns the decoded JSON response from the API (format preserved).
     * On connection/timeout failure, returns a structured error array with error_code -1.
     * On malformed JSON response, returns a structured error array with error_code -2.
     *
     * @param array $payload The request payload to send as JSON body.
     *
     * @return array The decoded JSON response on success, or a structured error array on failure.
     */
    private function sendRequest(array $payload): array
    {
        try {
            $response = $this->client->request('POST', $this->config->apiBaseUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'timeout'         => $this->config->requestTimeout,
                'connect_timeout' => $this->config->connectionTimeout,
                'body'            => $this->encodeJsonPayload($payload),
            ]);

            $body = $response->getBody();

            if (! is_string($body)) {
                return [
                    'error_code' => -2,
                    'error_msg'  => 'Invalid response from server.',
                    'data'       => null,
                ];
            }

            $decoded = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return [
                    'error_code' => -2,
                    'error_msg'  => 'Invalid response from server.',
                    'data'       => null,
                ];
            }

            return $decoded;
        } catch (HTTPException $e) {
            return [
                'error_code' => -1,
                'error_msg'  => $e->getMessage(),
                'data'       => null,
            ];
        }
    }

    /**
     * Encodes a payload as JSON, returning a validated string.
     *
     * @param array $payload The payload to encode.
     *
     * @return string The JSON-encoded string.
     *
     * @throws \RuntimeException If the payload cannot be encoded as JSON.
     */
    private function encodeJsonPayload(array $payload): string
    {
        $json = json_encode($payload);

        if ($json === false) {
            throw new \RuntimeException(
                'Failed to encode request payload as JSON: ' . json_last_error_msg()
            );
        }

        return $json;
    }
}
