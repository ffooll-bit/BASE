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
     * Retrieves the institution (PT) profile from the Neo Feeder API.
     *
     * Calls the API with the "GetProfilPT" action and the provided token.
     * All error cases (API error, connection failure, malformed response) are
     * handled by {@see sendRequest()} and returned as a structured error array.
     *
     * @param string $token The authentication token obtained from getToken().
     *
     * @return array The decoded API response on success, or a structured error array on failure.
     */
    public function getProfilPT(string $token): array
    {
        return $this->sendRequest([
            'act'   => 'GetProfilPT',
            'token' => $token,
        ]);
    }

    /**
     * Authenticates a user against the Neo Feeder API.
     *
     * Sends a GetToken action with the provided username and password.
     * Returns the raw response array from sendRequest().
     *
     * The password is passed directly to the HTTP request and is never
     * stored, logged, or retained beyond the single API call.
     *
     * @param string $username The user's email or username.
     * @param string $password The user's password.
     *
     * @return array The response array from the API or a structured error array.
     */
    public function getToken(string $username, string $password): array
    {
        return $this->sendRequest([
            'act'      => 'GetToken',
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * Sends a list-style request (Get action with optional filter/order/limit/offset).
     *
     * @param string $act     The API action name.
     * @param string $token   The authentication token.
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    private function sendListRequest(string $act, string $token, array $options = []): array
    {
        $payload = ['act' => $act, 'token' => $token];
        foreach (['filter', 'order', 'limit', 'offset'] as $key) {
            if (array_key_exists($key, $options)) {
                $payload[$key] = $options[$key];
            }
        }

        return $this->sendRequest($payload);
    }

    /**
     * Retrieves the list of students (Daftar Mahasiswa) from the Neo Feeder API.
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getListMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetListMahasiswa', $token, $options);
    }

    /**
     * Retrieves the student course activities (Aktivitas Kuliah Mahasiswa) from the Neo Feeder API.
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getAktivitasKuliahMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetAktivitasKuliahMahasiswa', $token, $options);
    }

    /**
     * Retrieves the graduated/dropped-out student list (Daftar Mahasiswa Lulus/DO) from the Neo Feeder API.
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getListMahasiswaLulusDO(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetListMahasiswaLulusDO', $token, $options);
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
