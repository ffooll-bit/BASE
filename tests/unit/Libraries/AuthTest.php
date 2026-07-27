<?php

namespace Tests\Unit\Libraries;

use App\Libraries\Auth;
use App\Libraries\NeoFeeder;
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;

class AuthTest extends CIUnitTestCase
{
    private NeoFeeder $neoFeeder;
    private Session $mockSession;
    private Auth $auth;

    protected function setUp(): void
    {
        parent::setUp();

        config('NeoFeeder')->validationTTL = 300;

        $this->neoFeeder    = $this->createMock(NeoFeeder::class);
        $this->mockSession = $this->createMock(Session::class);

        $encryption = new class () implements EncrypterInterface {
            public string $key = 'test-key-32-chars-for-hmac!!!!!';

            public function encrypt($data, $params = null): string
            {
                return base64_encode((string) $data);
            }

            public function decrypt($data, $params = null): string
            {
                return base64_decode((string) $data, true) ?: '';
            }
        };

        $this->auth = new Auth($this->neoFeeder, $this->mockSession, $encryption);
    }

    public function testLoginReturnsTrueOnValidCredentials(): void
    {
        $this->neoFeeder->method('getToken')
            ->willReturn(['error_code' => 0, 'data' => ['token' => 'valid-token']]);

        $this->mockSession->expects($this->once())->method('regenerate');
        $this->mockSession->expects($this->once())->method('set')
            ->with('auth', self::arrayHasKey('token'));

        $result = $this->auth->login('admin', 'password');

        $this->assertTrue($result);
        $this->assertNull($this->auth->getLastError());
    }

    public function testLoginReturnsFalseOnEmptyInput(): void
    {
        $this->neoFeeder->expects($this->never())->method('getToken');
        $this->mockSession->expects($this->never())->method('set');

        $result = $this->auth->login('', '');

        $this->assertFalse($result);
        $this->assertNotNull($this->auth->getLastError());
    }

    public function testLoginReturnsFalseOnConnectionError(): void
    {
        $this->neoFeeder->method('getToken')
            ->willReturn(['error_code' => -1, 'error_msg' => 'Connection failed', 'data' => null]);

        $this->mockSession->expects($this->never())->method('set');

        $result = $this->auth->login('admin', 'password');

        $this->assertFalse($result);
        $this->assertStringContainsString('Unable to connect', $this->auth->getLastError());
    }

    public function testLoginReturnsFalseOnInvalidCredentials(): void
    {
        $this->neoFeeder->method('getToken')
            ->willReturn(['error_code' => 1, 'error_msg' => 'Login failed', 'data' => null]);

        $this->mockSession->expects($this->never())->method('set');

        $result = $this->auth->login('admin', 'wrong-password');

        $this->assertFalse($result);
        $this->assertStringContainsString('Login failed', $this->auth->getLastError());
    }

    public function testIsLoggedInReturnsTrueWhenSessionExistsAndWithinTTL(): void
    {
        $this->mockSession->method('get')->willReturnMap([
            ['auth.token', null, 'some-token'],
            ['auth.lastValidatedAt', null, time()],
        ]);

        $this->neoFeeder->expects($this->never())->method('getProfilPT');

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testIsLoggedInReturnsFalseWhenNoSessionToken(): void
    {
        $this->mockSession->method('get')->with('auth.token')->willReturn(null);

        $this->assertFalse($this->auth->isLoggedIn());
    }

    public function testIsLoggedInValidatesTokenWhenTTLExpired(): void
    {
        $this->mockSession->method('get')->willReturnMap([
            ['auth.token', null, 'some-token'],
            ['auth.lastValidatedAt', null, time() - 1000],
        ]);

        $this->neoFeeder->method('getProfilPT')
            ->willReturn(['error_code' => 0, 'data' => ['nama_pt' => 'Test']]);

        $this->mockSession->expects($this->once())->method('set')
            ->with('auth.lastValidatedAt', self::isType('int'));

        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testValidateTokenReturnsFalseOnExpiredSession(): void
    {
        $this->mockSession->method('get')->willReturnMap([
            ['auth.token', null, 'expired-token'],
            ['auth.lastValidatedAt', null, time() - 1000],
        ]);

        $this->neoFeeder->method('getProfilPT')
            ->willReturn(['error_code' => 100, 'error_msg' => 'Token expired', 'data' => null]);

        $this->mockSession->expects($this->once())->method('remove')->with('auth');

        $this->assertFalse($this->auth->isLoggedIn());
        $this->assertSame('session expired', $this->auth->getLastError());
    }

    public function testLogoutClearsAuthSession(): void
    {
        $this->mockSession->expects($this->once())->method('destroy');

        $this->auth->logout();
    }
}
