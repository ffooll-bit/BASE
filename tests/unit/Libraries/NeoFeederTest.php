<?php

namespace Tests\Unit\Libraries;

use App\Libraries\NeoFeeder;
use CodeIgniter\HTTP\CURLRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use Config\NeoFeeder as NeoFeederConfig;

class NeoFeederTest extends CIUnitTestCase
{
    private NeoFeederConfig $config;
    private CURLRequest $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new NeoFeederConfig();
        $this->config->apiBaseUrl = 'https://example.com/api';
        $this->config->connectionTimeout = 1;
        $this->config->requestTimeout = 1;
    }

    public function testGetTokenReturnsTokenOnSuccess(): void
    {
        $responseBody = json_encode([
            'error_code' => 0,
            'data'       => ['token' => 'abc123'],
        ]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getToken('user', 'pass');

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('abc123', $result['data']['token']);
    }

    public function testGetTokenReturnsConnectionErrorOnFailure(): void
    {
        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willThrowException(
            new HTTPException('Connection timed out')
        );

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getToken('user', 'pass');

        $this->assertSame(-1, $result['error_code']);
        $this->assertStringContainsString('Connection timed out', $result['error_msg']);
    }

    public function testGetTokenReturnsParseErrorOnMalformedResponse(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn('not-json');

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getToken('user', 'pass');

        $this->assertSame(-2, $result['error_code']);
    }

    public function testGetProfilPTReturnsProfileOnSuccess(): void
    {
        $responseBody = json_encode([
            'error_code' => 0,
            'data'       => ['kode_pt' => '123456'],
        ]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getProfilPT('token-abc');

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('123456', $result['data']['kode_pt']);
    }

    public function testGetListMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode([
            'error_code' => 0,
            'data'       => [['nim' => '201731009', 'nama_mahasiswa' => 'Joko']],
        ]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getListMahasiswa('token-abc', ['limit' => 20, 'offset' => 0]);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('201731009', $result['data'][0]['nim']);
    }

    public function testGetAktivitasKuliahMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode([
            'error_code' => 0,
            'data'       => [['nim' => '201731009', 'ips' => '3.50']],
        ]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getAktivitasKuliahMahasiswa('token-abc', ['limit' => 20, 'offset' => 0]);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('3.50', $result['data'][0]['ips']);
    }

    public function testGetListMahasiswaLulusDOReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode([
            'error_code' => 0,
            'data'       => [['nim' => '201731009', 'nama_jenis_keluar' => 'Lulus']],
        ]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->getListMahasiswaLulusDO('token-abc', ['limit' => 20, 'offset' => 0]);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('Lulus', $result['data'][0]['nama_jenis_keluar']);
    }

    public function testInsertMahasiswaLulusDOReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode([
            'error_code' => 0,
            'data'       => ['nim' => '201731009'],
        ]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->insertMahasiswaLulusDO('token-abc', ['nim' => '201731009']);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('201731009', $result['data']['nim']);
    }

    public function testInsertBiodataMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode(['error_code' => 0, 'data' => ['id_mahasiswa' => 'uuid-1']]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->insertBiodataMahasiswa('token-abc', ['nama_mahasiswa' => 'Budi']);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('uuid-1', $result['data']['id_mahasiswa']);
    }

    public function testUpdateBiodataMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode(['error_code' => 0, 'data' => ['id_mahasiswa' => 'uuid-1']]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->updateBiodataMahasiswa('token-abc', 'uuid-1', ['nama_mahasiswa' => 'Budi']);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('uuid-1', $result['data']['id_mahasiswa']);
    }

    public function testDeleteBiodataMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode(['error_code' => 0, 'data' => ['id_mahasiswa' => 'uuid-1']]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->deleteBiodataMahasiswa('token-abc', 'uuid-1');

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('uuid-1', $result['data']['id_mahasiswa']);
    }

    public function testInsertPerkuliahanMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode(['error_code' => 0, 'data' => ['id_registrasi_mahasiswa' => 'r-1']]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->insertPerkuliahanMahasiswa('token-abc', ['id_registrasi_mahasiswa' => 'r-1']);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('r-1', $result['data']['id_registrasi_mahasiswa']);
    }

    public function testUpdatePerkuliahanMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode(['error_code' => 0, 'data' => ['id_registrasi_mahasiswa' => 'r-1']]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->updatePerkuliahanMahasiswa('token-abc', 'r-1', '20231', ['ips' => '3.5']);

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('r-1', $result['data']['id_registrasi_mahasiswa']);
    }

    public function testDeletePerkuliahanMahasiswaReturnsDataOnSuccess(): void
    {
        $responseBody = json_encode(['error_code' => 0, 'data' => ['id_registrasi_mahasiswa' => 'r-1']]);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getBody')->willReturn($responseBody);

        $this->client = $this->createStub(CURLRequest::class);
        $this->client->method('request')->willReturn($response);

        $neoFeeder = new NeoFeeder($this->config, $this->client);
        $result    = $neoFeeder->deletePerkuliahanMahasiswa('token-abc', 'r-1', '20231');

        $this->assertSame(0, $result['error_code']);
        $this->assertSame('r-1', $result['data']['id_registrasi_mahasiswa']);
    }
}
