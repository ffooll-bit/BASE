<?php

namespace Tests\Unit\Filters;

use App\Filters\AuthFilter;
use App\Libraries\Auth;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

class AuthFilterTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testBeforeReturnsNullWhenAuthenticated(): void
    {
        $mockAuth = $this->createMock(Auth::class);
        $mockAuth->method('isLoggedIn')->willReturn(true);

        Services::injectMock('auth', $mockAuth);

        $filter  = new AuthFilter();
        $request = service('request');

        $result = $filter->before($request);

        $this->assertNull($result);
    }

    public function testBeforeRedirectsToLoginWhenNotAuthenticated(): void
    {
        $mockAuth = $this->createMock(Auth::class);
        $mockAuth->method('isLoggedIn')->willReturn(false);
        $mockAuth->method('hasPriorAuthCookie')->willReturn(false);

        Services::injectMock('auth', $mockAuth);

        $filter  = new AuthFilter();
        $request = service('request');

        $result = $filter->before($request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertSame(302, $result->getStatusCode());
    }
}
