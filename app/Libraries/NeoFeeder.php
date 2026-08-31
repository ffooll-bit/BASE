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
     * Builds and executes a GET request to a Neo Feeder cloud REST endpoint.
     *
     * The cloud REST API (e.g. /ws/transkrip/*) authenticates via a Browser
     * Authorization header carrying the same token issued by the WS GetToken
     * action. On success returns the decoded JSON response; on failure a
     * structured error array with the same contract as sendRequest().
     *
     * @param string $url   The endpoint URL (without query string).
     * @param array  $query Query parameters to append to the URL.
     * @param string $token The authentication token from getToken().
     *
     * @return array The decoded JSON response on success, or a structured error array on failure.
     */
    private function cloudGet(string $url, array $query, string $token): array
    {
        $url .= '?' . http_build_query($query);

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'timeout'         => $this->config->requestTimeout,
                'connect_timeout' => $this->config->connectionTimeout,
            ]);

            if ($response->getStatusCode() === 401) {
                return [
                    'error_code' => 100,
                    'error_msg'  => 'Session Neo Feeder telah berakhir.',
                    'data'       => null,
                ];
            }

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
     * Retrieves the list of student statuses (Daftar Status Mahasiswa) from the Neo Feeder API.
     *
     * Options are the standard filter/order/limit/offset overrides.
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getStatusMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetStatusMahasiswa', $token, $options);
    }

    /**
     * Retrieves the graduation exit-reason reference list (id_jenis_keluar + jenis_keluar).
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response, or a structured error array.
     */
    public function getJenisKeluar(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetJenisKeluar', $token, $options);
    }

    /**
     * Retrieves the academic semester reference list (id_semester + nama_semester).
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response, or a structured error array.
     */
    public function getSemester(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetSemester', $token, $options);
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
     * Returns the total count of students matching a filter (GetCountMahasiswa).
     *
     * @param string $token   The authentication token.
     * @param array  $options Optional filter/order overrides (no limit/offset).
     *
     * @return array The decoded API response.
     */
    public function getCountMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetCountMahasiswa', $token, $options);
    }

    /**
     * Returns the total count of student course activities (GetCountAktivitasMahasiswa).
     *
     * @param string $token   The authentication token.
     * @param array  $options Optional filter/order overrides (no limit/offset).
     *
     * @return array The decoded API response.
     */
    public function getCountAktivitasKuliahMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetCountAktivitasMahasiswa', $token, $options);
    }

    /**
     * Returns the total count of graduated/dropped-out students (GetCountMahasiswaLulusDO).
     *
     * @param string $token   The authentication token.
     * @param array  $options Optional filter/order overrides (no limit/offset).
     *
     * @return array The decoded API response.
     */
    public function getCountMahasiswaLulusDO(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetCountMahasiswaLulusDO', $token, $options);
    }

    /**
     * Retrieves a student's academic transcript (GetTranskripMahasiswa).
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getTranskripMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetTranskripMahasiswa', $token, $options);
    }

    /**
     * Retrieves a student's per-course grades from the Neo Feeder cloud
     * "Cek Transkrip Mahasiswa" menu.
     *
     * The cloud REST endpoint (GET /ws/transkrip/nilai_mahasiswa) exposes the
     * per-course `choosed` marker ("included in transcript") that the WS act
     * GetTranskripMahasiswa does not return. Authentication uses the same WS
     * token but as an Authorization Bearer header on the cloud REST endpoint.
     *
     * @param string $token The authentication token obtained from getToken().
     * @param string $nim   The student NIM to look up.
     *
     * @return array The decoded course rows on success (`data`), or a structured error array.
     */
    public function getCekTranskripMahasiswa(string $token, string $nim): array
    {
        $cloudBase = rtrim(dirname($this->config->apiBaseUrl), '/');

        $search = $this->cloudGet($cloudBase . '/transkrip/cari_mahasiswa', ['nm_pd' => $nim], $token);
        if (isset($search['error_code'])) {
            return $search;
        }

        $student = null;
        foreach ($search['list_mahasiswa'] ?? [] as $group) {
            foreach (is_array($group) ? $group : [] as $candidate) {
                if (is_array($candidate) && rtrim((string) ($candidate['nipd'] ?? '')) === $nim) {
                    $student = $candidate;
                    break 2;
                }
            }
        }

        if ($student === null) {
            return [
                'error_code' => -1,
                'error_msg'  => 'Mahasiswa tidak ditemukan pada Cek Transkrip Mahasiswa.',
                'data'       => null,
            ];
        }

        $grades = $this->cloudGet(
            $cloudBase . '/transkrip/nilai_mahasiswa',
            ['mahasiswa' => json_encode($student)],
            $token
        );
        if (isset($grades['error_code'])) {
            return $grades;
        }

        $rows = $grades['nilai_mahasiswa'][0] ?? $grades['nilai_mahasiswa'] ?? [];

        return [
            'error_code' => 0,
            'error_msg'  => '',
            'data'       => $rows,
        ];
    }

    /**
     * Retrieves a single student's full biodata (GetBiodataMahasiswa).
     *
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $options Optional filter/order/limit/offset overrides.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getBiodataMahasiswa(string $token, array $options = []): array
    {
        return $this->sendListRequest('GetBiodataMahasiswa', $token, $options);
    }

    /**
     * Retrieves a single student course-activity record (GetDetailPerkuliahanMahasiswa).
     *
     * @param string $token The authentication token obtained from getToken().
     * @param array  $key   The primary-key fields (id_registrasi_mahasiswa, id_semester).
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function getDetailPerkuliahanMahasiswa(string $token, array $key): array
    {
        $idReg = str_replace("'", "\'", (string) ($key['id_registrasi_mahasiswa'] ?? ''));
        $idSmt = str_replace("'", "\'", (string) ($key['id_semester'] ?? ''));
        $filter = "id_registrasi_mahasiswa='{$idReg}' AND id_semester='{$idSmt}'";

        return $this->sendListRequest('GetDetailPerkuliahanMahasiswa', $token, ['filter' => $filter]);
    }

    /**
     * Submits a graduated/dropped-out student record to the Neo Feeder API.
     *
     * @param string $token  The authentication token obtained from getToken().
     * @param array  $record The graduation record fields (InsertMahasiswaLulusDO).
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function insertMahasiswaLulusDO(string $token, array $record): array
    {
        return $this->sendRequest([
            'act'    => 'InsertMahasiswaLulusDO',
            'token'  => $token,
            'record' => $record,
        ]);
    }

    /**
     * Sends a mutation request (Insert/Update/Delete) to the Neo Feeder API.
     *
     * Builds the payload with the action, token, and the optional `key` (primary
     * key for Update/Delete) and `record` (fields for Insert/Update) fields, then
     * delegates to {@see sendRequest()}.
     *
     * @param string $act     The API action name (e.g. InsertBiodataMahasiswa).
     * @param string $token   The authentication token obtained from getToken().
     * @param array  $record  The record fields for Insert/Update. Null for Delete.
     * @param array  $key     The primary-key fields for Update/Delete. Empty for Insert.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    private function sendMutation(string $act, string $token, ?array $record = null, ?array $key = null): array
    {
        $payload = ['act' => $act, 'token' => $token];

        if ($key !== null) {
            $payload['key'] = $key;
        }

        if ($record !== null) {
            $payload['record'] = $record;
        }

        return $this->sendRequest($payload);
    }

    /**
     * Inserts a student biodata record (InsertBiodataMahasiswa).
     *
     * @param string $token  The authentication token obtained from getToken().
     * @param array  $record The biodata record fields.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function insertBiodataMahasiswa(string $token, array $record): array
    {
        return $this->sendMutation('InsertBiodataMahasiswa', $token, $record);
    }

    /**
     * Updates a student biodata record (UpdateBiodataMahasiswa).
     *
     * @param string $token       The authentication token obtained from getToken().
     * @param string $idMahasiswa The primary key (id_mahasiswa).
     * @param array  $record      The biodata record fields.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function updateBiodataMahasiswa(string $token, string $idMahasiswa, array $record): array
    {
        return $this->sendMutation('UpdateBiodataMahasiswa', $token, $record, ['id_mahasiswa' => $idMahasiswa]);
    }

    /**
     * Deletes a student biodata record (DeleteBiodataMahasiswa).
     *
     * @param string $token       The authentication token obtained from getToken().
     * @param string $idMahasiswa The primary key (id_mahasiswa).
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function deleteBiodataMahasiswa(string $token, string $idMahasiswa): array
    {
        return $this->sendMutation('DeleteBiodataMahasiswa', $token, null, ['id_mahasiswa' => $idMahasiswa]);
    }

    /**
     * Inserts a student course-activity record (InsertPerkuliahanMahasiswa).
     *
     * @param string $token  The authentication token obtained from getToken().
     * @param array  $record The perkuliahan record fields.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function insertPerkuliahanMahasiswa(string $token, array $record): array
    {
        return $this->sendMutation('InsertPerkuliahanMahasiswa', $token, $record);
    }

    /**
     * Updates a student course-activity record (UpdatePerkuliahanMahasiswa).
     *
     * @param string $token           The authentication token obtained from getToken().
     * @param string $idRegistrasi    The primary key (id_registrasi_mahasiswa).
     * @param string $idSemester      The primary key (id_semester).
     * @param array  $record          The perkuliahan record fields.
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function updatePerkuliahanMahasiswa(string $token, string $idRegistrasi, string $idSemester, array $record): array
    {
        return $this->sendMutation('UpdatePerkuliahanMahasiswa', $token, $record, [
            'id_registrasi_mahasiswa' => $idRegistrasi,
            'id_semester'             => $idSemester,
        ]);
    }

    /**
     * Updates the grade of a student in a class (UpdateNilaiPerkuliahanKelas).
     *
     * @param string $token       The authentication token obtained from getToken().
     * @param string $idRegistrasi The primary key (id_registrasi_mahasiswa).
     * @param string $idKelas      The primary key (id_kelas_kuliah).
     * @param array  $record       The class-grade record fields (e.g. nilai_huruf).
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function updateNilaiPerkuliahanKelas(string $token, string $idRegistrasi, string $idKelas, array $record): array
    {
        return $this->sendMutation('UpdateNilaiPerkuliahanKelas', $token, $record, [
            'id_registrasi_mahasiswa' => $idRegistrasi,
            'id_kelas_kuliah'         => $idKelas,
        ]);
    }

    /**
     * Deletes a student course-activity record (DeletePerkuliahanMahasiswa).
     *
     * @param string $token        The authentication token obtained from getToken().
     * @param string $idRegistrasi The primary key (id_registrasi_mahasiswa).
     * @param string $idSemester   The primary key (id_semester).
     *
     * @return array The decoded API response on success, or a structured error array.
     */
    public function deletePerkuliahanMahasiswa(string $token, string $idRegistrasi, string $idSemester): array
    {
        return $this->sendMutation('DeletePerkuliahanMahasiswa', $token, null, [
            'id_registrasi_mahasiswa' => $idRegistrasi,
            'id_semester'             => $idSemester,
        ]);
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
