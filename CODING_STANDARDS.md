# Coding Standards — BASE Project

Follow these strictly. Deviations must be justified by the Decision Framework in `AGENTS.md`.

---

## 1. PHP

### 1.1 Standard

PSR-12 (`@PSR12` in PHP CS Fixer). PHP 8.2+ required.

### 1.2 PHP 8.2 features — use these

Typed properties, union/nullable types (`?string`), match expressions, named arguments, `readonly` properties.

### 1.3 Features NOT required

`declare(strict_types=1)` — not used; may break existing code relying on type coercion. Attributes, enums, promoted constructor — ok for new code if adds value.

### 1.4 Never use

`dd()`, `var_dump()`, `print_r()`, `exit()`, `mixed` (unless unavoidable), `eval()`, `extract()`, `compact()`.

### 1.5 Naming

| Construct | Convention | Example |
|-----------|------------|---------|
| Classes | PascalCase | `AuthFilter` |
| Methods / properties / variables | camelCase | `isLoggedIn()`, `$lastError` |
| Constants | UPPER_SNAKE_CASE | `EXIT_ERROR` |
| Private methods | camelCase, no underscore | `validateToken()` not `_validateToken()` |

One class per file. Filename matches class name (PSR-4). Namespace follows directory path.

### 1.6 Use statements

Alphabetical order, single block, no blank-line grouping. No unused imports (caught by PHP CS Fixer).

### 1.7 Type hints

Required on all properties, parameters, and return types (except controllers returning concatenated views may omit).

### 1.8 Comments

PHPDoc on every public/protected method with `@param` and `@return`.
Inline comments (`//`) for WHY, not WHAT.
Ponytail comments (`// ponytail: ...`) for deliberate shortcuts.

---

## 2. CodeIgniter 4 Conventions

### 2.1 Service access

```php
$auth = service('auth');
$neoFeeder = service('neoFeeder');
```

Use `service()` — never `new` for registered services.

### 2.2 Config access

```php
$ttl = config('NeoFeeder')->validationTTL;
```

### 2.3 View rendering

Concatenate partials (no template inheritance):

```php
return view('layout/header')
    . view('layout/sidebar')
    . view('page/content', ['data' => $data])
    . view('layout/footer');
```

### 2.4 Routing

All routes explicit in `app/Config/Routes.php`. No `$routes->autoRoute()`.

### 2.5 CSRF

Every POST form includes `<?= csrf_field() ?>` immediately after `<form>`.

### 2.6 Validation

```php
if (! $this->validate([
    'username' => 'required|valid_email',
    'password' => 'required|min_length[6]',
])) {
    return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
}
```

### 2.7 Request input

Use `$this->request->getPost('name')` / `getVar('name')` — never `$_POST` / `$_GET`.

### 2.8 Redirects

```php
return redirect()->to('/dashboard');
return redirect()->back()->withInput()->with('error', $message);
```

---

## 3. Views

### 3.1 Output escaping

ALL dynamic output uses `esc()`:

```php
<?= esc($username) ?>                    // html context (default)
<?= esc($value, 'url') ?>                // URL
<?= esc($value, 'attr') ?>               // HTML attribute
```

### 3.2 Short tags

Use `<?= ?>` for output (never `<?php echo`).

### 3.3 Alternate control structures

```php
<?php if (condition): ?>
    <p><?= esc($value) ?></p>
<?php endif; ?>

<?php foreach ($items as $item): ?>
    <tr><td><?= esc($item) ?></td></tr>
<?php endforeach; ?>
```

### 3.4 Asset URLs

```php
<link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
```

### 3.5 Layout assembly

4 partials: `view('layout/header') + view('layout/sidebar') + view('{page}') + view('layout/footer')`.

Page-specific view structure:

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

### 4.2 Vanilla JS only

```javascript
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('myButton').addEventListener('click', handleClick);
});
```

### 4.3 Bootstrap 5 components

Use `data-bs-*` attributes. Use Bootstrap 5 native API for modals:

```javascript
var modal = new bootstrap.Modal(document.getElementById('modalId'));
modal.show();
```

### 4.4 Debug code

`console.log()` must never reach a commit.

---

## 5. CSS

### 5.1 Bootstrap utility-first

Build layouts with Bootstrap 5 utilities before writing custom CSS.

### 5.2 AdminLTE 4 layout classes

| Purpose | Class |
|---------|-------|
| Wrapper | `app-wrapper` |
| Header | `app-header` |
| Sidebar | `app-sidebar` |
| Content | `app-main` |
| Sidebar menu | `sidebar-menu` |
| Sidebar brand | `sidebar-brand` |

### 5.3 Custom CSS

If necessary: BEM naming, single file (`public/css/app.css`), no inline styles.

### 5.4 Icons

Font Awesome 6. Solid (`fas`) for navigation/buttons/alerts. Regular (`far`) for nested sidebar children. Sidebar icons include `nav-icon` class.

---

## 6. Error Handling

API error return pattern — structured arrays:

```php
// Success
return ['error_code' => 0, 'error_msg' => '', 'data' => $result];
// Failure
return ['error_code' => -1, 'error_msg' => 'Connection failed', 'data' => null];
```

Wrap all external API calls in `try-catch` catching `HTTPException`.
Use `\RuntimeException` for unexpected states that should crash.
User-facing errors: `return redirect()->to('/login')->with('error', $message)`.

---

## 7. Anti-Patterns

| # | Don't | Do |
|---|-------|----|
| 1 | `dd()`, `var_dump()`, `print_r()`, `exit()` | Remove before commit |
| 2 | `new ServiceClass()` for registered services | `service('name')` |
| 3 | `$this->load->view()` (CI3) | `view('name', $data)` |
| 4 | Hardcoded URLs like `/public/index.php/dashboard` | `base_url('dashboard')` |
| 5 | Query string CSRF | Cookie-based CSRF with `csrf_field()` |
| 6 | PHP closing tag `?>` at end of pure PHP files | Omit |
| 7 | `array()` syntax | `[]` short array |
| 8 | Bootstrap 4 utilities (`ml-*`, `float-right`, `data-toggle`) | BS5 equivalents (`ms-*`, `float-end`, `data-bs-toggle`) |
| 9 | AdminLTE 3 classes (`wrapper`, `main-sidebar`, `content-wrapper`) | AL4 classes (`app-wrapper`, `app-sidebar`, `app-main`) |
| 10 | `onclick` HTML attributes | `addEventListener('click', handler)` or `data-bs-*` |
| 11 | `for ($i=0; $i<count($x); $i++)` | `foreach ($items as $item)` |
| 12 | `config-item` / Laravel-style conditional classes | `<?= $condition ? 'active' : '' ?>` |

---

## 8. Tooling

### 8.1 PHP syntax check

```bash
php -l app/Controllers/Login.php          # on every changed PHP file
```

### 8.2 PHP CS Fixer

```bash
vendor/bin/php-cs-fixer fix               # auto-fix style
vendor/bin/php-cs-fixer fix --dry-run --diff  # preview changes
```

Config in `.php-cs-fixer.dist.php` (scans `app/` and `tests/`).

### 8.3 EditorConfig

Enforced automatically by supported editors:
- UTF-8 charset, CRLF line endings, 4-space indent, trim trailing whitespace, final newline.

---

**Reference:** PSR-12 | PHP CS Fixer | CI4 User Guide | AdminLTE 4 Docs
