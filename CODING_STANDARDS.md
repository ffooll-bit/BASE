# Coding Standards — BASE Project

This document defines the coding conventions for all PHP, JavaScript, CSS, and view files.
Follow these strictly. Deviations must be justified by the Decision Framework in `AGENTS.md`.

---

## 1. PHP

### 1.1 PSR-12

All PHP code MUST comply with **PSR-12** (`@PSR12` in PHP CS Fixer).

### 1.2 PHP 8.2 Features

Required PHP version is `^8.2`. Use these features:

| Feature | Required | Example |
|---------|----------|---------|
| Typed properties | Yes | `private NeoFeeder $neoFeeder;` |
| Union types / nullable | Yes | `private ?string $lastError = null;` |
| Match expressions | Where cleaner than switch | Prefer over `switch` |
| Named arguments | Where improves readability | `redirect()->with('error', $msg)` |
| `readonly` properties | For DTOs / config | `public readonly string $name` |

NOT required (use at discretion):

| Feature | Note |
|---------|------|
| `declare(strict_types=1)` | Not currently used; may break existing code that relies on type coercion |
| Attributes | Not used anywhere; ok for new code if adds value |
| Enums | Not used anywhere; ok for new code |
| Promoted constructor | Not used anywhere; ok for new code |

Do NOT use:

| Feature | Why |
|---------|-----|
| `var_dump()`, `dd()`, `print_r()`, `exit()` | Debug leftovers — must never reach a commit |
| `mixed` type (unless truly necessary) | Prevents static analysis |
| `eval()`, `extract()`, `compact()` | Security risk / debugging nightmare |

### 1.3 Naming

| Construct | Convention | Example |
|-----------|------------|---------|
| Classes | PascalCase | `AuthFilter`, `NeoFeeder` |
| Interfaces | PascalCase | `FilterInterface` (CI4 built-in) |
| Methods | camelCase | `isLoggedIn()`, `getToken()` |
| Properties | camelCase | `$lastError`, `$apiBaseUrl` |
| Variables | camelCase | `$username`, `$responseData` |
| Constants | UPPER_SNAKE_CASE | `SECOND`, `EXIT_ERROR` |
| Config properties | camelCase | `$baseURL`, `$connectionTimeout` |
| Private methods | camelCase (no underscore prefix) | `validateToken()` not `_validateToken()` |

### 1.4 File Structure

- One class per file (no exceptions).
- Filename matches class name (PSR-4 autoloading).
- Namespace follows directory path:
  - `app/Controllers/` → `namespace App\Controllers;`
  - `app/Libraries/` → `namespace App\Libraries;`
  - `app/Filters/` → `namespace App\Filters;`
  - `app/Config/` → `namespace Config;`

### 1.5 Use Statements

- Alphabetical order.
- Single block (no blank-line grouping).
- No unused imports — PHP CS Fixer's `no_unused_imports` rule catches these.

```php
use CodeIgniter\Encryption\EncrypterInterface;
use CodeIgniter\Session\Session;
```

### 1.6 Type Hints

REQUIRED on:

- All class properties (typed properties)
- All method parameters
- All method return types

```php
public function login(string $username, string $password): bool
{
    // ...
}

private function encodeJsonPayload(array $payload): string
{
    // ...
}
```

Controllers MAY omit return types for `index()` methods when returning views via concatenation,
but SHOULD type them when returning a single value:

```php
// Acceptable (return type optional for concatenated views):
public function index()
{
    return view('layout/header')
        . view('login/login')
        . view('layout/footer');
}

// Required on all other methods:
public function attemptLogin(): ?RedirectResponse
{
    // ...
}

public function index(): string  // also acceptable
{
    return view('login/login');
}
```

### 1.7 Comments

- **PHPDoc** on every method that is `public` or `protected`, with `@param` and `@return` where applicable.
- **Inline comments** (`//`) for non-obvious logic (WHY, not WHAT).
- **Ponytail comments** (`// ponytail: ...`) to document deliberate shortcuts.
- Do NOT write comments that restate the code. Let the code speak.

```php
/**
 * Attempts to authenticate a user with the given username and password.
 *
 * Validates inputs, requests a token from Neo Feeder, and stores
 * session data on success. The password is never stored, logged, or retained.
 *
 * @param string $username The user's username or email.
 * @param string $password The user's password.
 *
 * @return bool True if authentication succeeds, false on failure.
 */
public function login(string $username, string $password): bool
{
    // ...
    // ponytail: Global lock, per-account locks if throughput matters
}
```

---

## 2. CodeIgniter 4 Conventions

### 2.1 Service Access

Use the `service()` helper — never `new` for registered services:

```php
$auth = service('auth');
$neoFeeder = service('neoFeeder');
$session = service('session');
```

### 2.2 Config Access

Use the `config()` helper:

```php
$ttl = config('NeoFeeder')->validationTTL;
```

### 2.3 View Rendering

Use string concatenation of partial views (not template inheritance):

```php
return view('layout/header')
    . view('layout/sidebar')
    . view('page/content', ['data' => $data])
    . view('layout/footer');
```

Pass data as the second argument to `view()`:

```php
view('dashboard/index', ['username' => $username])
```

### 2.4 Routing

Define all routes explicitly in `app/Config/Routes.php`:

```php
$routes->get('path', 'Controller::method');
$routes->post('path', 'Controller::method');
```

Do NOT use `$routes->autoRoute()`.

### 2.5 CSRF

All `POST` forms MUST include `csrf_field()` immediately after the `<form>` tag:

```php
<form action="<?= base_url('login') ?>" method="post">
    <?= csrf_field() ?>
    <!-- fields -->
</form>
```

### 2.6 Validation

Use CI4 Validation library:

```php
if (! $this->validate([
    'username' => 'required|valid_email',
    'password' => 'required|min_length[6]',
])) {
    return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
}
```

### 2.7 Redirects

```php
// Success → redirect with optional flashdata
return redirect()->to('/dashboard');

// Failure → redirect back with input + error
return redirect()->back()->withInput()->with('error', $message);

// With flashdata
return redirect()->to('/login')->with('error', service('auth')->getLastError());
```

---

## 3. Views

### 3.1 Output Escaping

**ALL** dynamic output MUST use `esc()`:

```php
<?= esc($username) ?>
<?= esc(session()->getFlashdata('error')) ?>
<?= esc(session('auth.username')) ?>
```

The second parameter specifies the context (default `'html'`):

```php
esc($value, 'js')      // For JavaScript string literals
esc($value, 'url')     // For URL parameters
```

### 3.2 Short Tags

Use `<?= ?>` for output (never `<?php echo`).

### 3.3 Alternate Control Structures

Use colon syntax for control structures in views:

```php
<?php if (condition): ?>
    <p><?= esc($value) ?></p>
<?php endif; ?>

<?php foreach ($items as $item): ?>
    <tr>
        <td><?= esc($item) ?></td>
    </tr>
<?php endforeach; ?>
```

### 3.4 Asset URLs

Always use `base_url()`:

```php
<link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
<a href="<?= base_url('dashboard') ?>">Dashboard</a>
```

### 3.5 Indentation

4 spaces per level (no tabs). Enforced by `.editorconfig`.

### 3.6 Layout Assembly

The page is assembled from 4 partials:

```
view('layout/header') + view('layout/sidebar') + view('{page}') + view('layout/footer')
```

Page-specific views must follow this markup structure:

```html
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($title) ?></h3>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <!-- Cards, tables, forms -->
    </div>
</div>
```

---

## 4. JavaScript

### 4.1 jQuery is BANNED

No jQuery. No `$()` calls. Zero exceptions.

### 4.2 Vanilla JS

```javascript
// Correct
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('myButton').addEventListener('click', handleClick);
});

// Wrong — inline event handler
<button onclick="handleClick()">Save</button>
```

### 4.3 Bootstrap 5 Data Attributes

Use `data-bs-*` attributes for Bootstrap components:

```html
<div class="dropdown-menu" data-bs-toggle="dropdown">
<button type="button" class="btn-close" data-bs-dismiss="alert">
```

### 4.4 Debug Code

`console.log()` must never reach a commit.

### 4.5 Modal Control

Use Bootstrap 5's native JS API:

```javascript
var modal = new bootstrap.Modal(document.getElementById('modalId'));
modal.show();
modal.hide();
```

---

## 5. CSS

### 5.1 Bootstrap Utility-First

Build layouts with Bootstrap 5 utility classes before writing custom CSS:

```html
<div class="d-flex justify-content-between align-items-center mb-3">
```

### 5.2 AdminLTE 4 Markup

Use AdminLTE 4 classes for layout shell:

| Purpose | Class |
|---------|-------|
| Wrapper | `app-wrapper` |
| Header | `app-header` |
| Sidebar | `app-sidebar` |
| Content | `app-main` |
| Sidebar menu | `sidebar-menu` |
| Sidebar brand | `sidebar-brand` |

### 5.3 Custom CSS

If custom CSS is necessary (avoid when possible):

- Use BEM naming: `.block__element--modifier`
- Place in a single file: `public/assets/css/app.css`
- No inline styles except for programmatic dynamic values (e.g., avatar dimensions)

### 5.4 Icons

Use Font Awesome 6 classes:

```html
<i class="fas fa-edit"></i>    <!-- Solid — default -->
<i class="far fa-circle"></i>  <!-- Regular — lower emphasis -->
<i class="fab fa-google"></i>  <!-- Brands — third-party logos -->
```

Sidebar icons must include `nav-icon` class:

```html
<i class="nav-icon fas fa-tachometer-alt"></i>
```

---

## 6. Error Handling

### 6.1 API Error Return Pattern

All service methods that interact with external APIs return structured arrays:

```php
// Success
return ['error_code' => 0, 'error_msg' => '', 'data' => $result];

// Failure
return ['error_code' => -1, 'error_msg' => 'Connection failed', 'data' => null];
```

### 6.2 Try-Catch

Wrap all external API calls in `try-catch`:

```php
try {
    $response = $this->client->request('POST', $url, $options);
} catch (HTTPException $e) {
    return $this->errorResponse(-1, $e->getMessage());
}
```

### 6.3 Exceptions

Use `\RuntimeException` for unexpected states that should crash (not recover):

```php
if ($json === false) {
    throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
}
```

### 6.4 User-Facing Errors

Use flashdata with `redirect()->with()`:

```php
return redirect()->to('/login')->with('error', $message);
```

---

## 7. Anti-Patterns

| # | Don't | Do |
|---|-------|----|
| 1 | `var_dump()`, `dd()`, `print_r()`, `exit()` | Remove before commit; `php -l` and self-review catch these |
| 2 | `new ServiceClass()` for registered services | `service('name')` — singletons are managed by CI4 |
| 3 | `$this->load->view()` (CI3 pattern) | `view('name', $data)` |
| 4 | Hardcoded URLs like `/public/index.php/dashboard` | `base_url('dashboard')` |
| 5 | Raw `echo` in controllers | Return view strings or redirect |
| 6 | Query string CSRF (CI4 default in some configs) | Cookie-based CSRF with `csrf_field()` |
| 7 | PHP closing tag `?>` at end of pure PHP files | Omit — prevents accidental whitespace output |
| 8 | `array()` syntax | `[]` short array syntax |
| 9 | `SELECT *` in raw queries | Explicit column list |
| 10 | Bootstrap 4 utilities (`ml-*`, `float-right`, `data-toggle`) | Bootstrap 5 equivalents (`ms-*`, `float-end`, `data-bs-toggle`) |
| 11 | AdminLTE 3 classes (`wrapper`, `main-sidebar`, `content-wrapper`) | AdminLTE 4 classes (`app-wrapper`, `app-sidebar`, `app-main`) |
| 12 | `onclick` HTML attributes | `addEventListener('click', handler)` or `data-bs-*` attributes |
| 13 | `for ($i=0; $i<count($items); $i++)` | `foreach ($items as $item)` |
| 14 | `config-item` or `class="..." {{ $condition ? 'active' : '' }}"` (Laravel-style) | `<?= $condition ? 'active' : '' ?>` |

---

## 8. Tooling

### 8.1 PHP Syntax Check

Run `php -l` on every PHP file created or modified:

```bash
php -l app/Controllers/Login.php
```

### 8.2 PHP CS Fixer

Run before commit to auto-fix style issues:

```bash
vendor/bin/php-cs-fixer fix
```

To preview changes without applying:

```bash
vendor/bin/php-cs-fixer fix --dry-run --diff
```

The config is in `.php-cs-fixer.dist.php` at the project root. It scans `app/` and `tests/`.

### 8.3 EditorConfig

Supported editors (VS Code, PHPStorm, Sublime, etc.) read `.editorconfig` automatically.
It enforces:

- UTF-8 charset
- CRLF line endings
- 4-space indentation
- Trailing whitespace trimmed
- Final newline on save

---

**Reference:**
- PSR-12: https://www.php-fig.org/psr/psr-12/
- PHP CS Fixer: https://cs.symfony.com/
- EditorConfig: https://editorconfig.org/
- CI4 User Guide: https://codeigniter.com/user_guide/
- AdminLTE 4 Docs: https://adminlte-v4.netlify.app/docs/
