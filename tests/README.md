# Testing — BASE Project

## Quick Start

```bash
vendor/bin/phpunit                    # Run all tests
vendor/bin/phpunit tests/unit/AuthTest.php  # Run specific test file
vendor/bin/phpunit --filter=testLogin  # Run specific test method
```

## Test Structure

```
tests/
├── README.md           # This file
├── unit/               # Unit tests (Auth, NeoFeeder, AuthFilter)
│   ├── AuthTest.php
│   ├── NeoFeederTest.php
│   └── AuthFilterTest.php
└── _support/           # Test helpers, mocks, database config
```

## Writing Tests

### Base Class

All tests extend `CIUnitTestCase` — CodeIgniter 4's test base class.

### Mocking Pattern

Services are injected via CI4's `Services` class. Mock a service like this:

```php
// Mock NeoFeeder service to return a successful token
$neoFeederMock = $this->getMockBuilder(NeoFeeder::class)
    ->disableOriginalConstructor()
    ->getMock();
$neoFeederMock->method('getToken')
    ->willReturn(['error_code' => 0, 'error_msg' => '', 'data' => ['token' => 'abc']]);
Services::injectMock('neoFeeder', $neoFeederMock);
```

### Reset Between Tests

Call `Services::reset()` in `setUp()` to clear injected mocks:

```php
protected function setUp(): void
{
    parent::setUp();
    Services::reset();
}
```

## What to Test

| Component | Test Pattern | Example |
|-----------|-------------|---------|
| `Auth::login()` | Mock NeoFeeder, assert session data | `AuthTest::testLoginSuccess()` |
| `Auth::isLoggedIn()` | Set session data, assert true/false | `AuthTest::testIsLoggedInWithValidSession()` |
| `NeoFeeder::getToken()` | Mock CURLRequest, assert parsed response | `NeoFeederTest::testGetTokenParsesSuccess()` |
| `AuthFilter::before()` | Assert redirect on no session, passthrough with session | `AuthFilterTest::testRedirectWhenNotLoggedIn()` |

## Coverage Target

Minimum 70% code coverage for `app/Libraries/` and `app/Filters/`.

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `Class not found` | Run `composer dump-autoload` before tests |
| `Headers already sent` | Ensure no output before test assertions |
| `Session not available` | Some tests need `$this->session = $this->createMock(Session::class)` |
