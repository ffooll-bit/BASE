# Open Issues / Backlog

Priority levels: **P-1** (urgent), **P-2** (important), **P-3** (nice to have).

---

## Open Items (Aktif)

_Kosong — tidak ada task yang sedang dikerjakan._

> Untuk menambah task baru: salin template di bawah ke bagian ini, isi detailnya, lalu commit. Agent akan membaca dan mengerjakan task pada sesi berikutnya sesuai prioritas.

### Template Task Baru

```markdown
### ISS-NNN: Judul Task — P-{1|2|3}

**Status:** Open
**Created:** YYYY-MM-DD
**Estimate:** {S|M|L|XL}
**Depends on:** —

### Description

Apa yang perlu dibuat/diubah/diperbaiki.

### Files to touch (perkiraan)

| File | Perubahan |
|------|-----------|
| `app/Controllers/X.php` | ... |
| `app/Views/X.php` | ... |

### Acceptance Criteria

- [ ] Criterion 1
- [ ] Criterion 2
```

---

## Sprint 1 (2026-07-27) — Project Preparation & Core Setup

Semua item di bawah sudah selesai dan diarsipkan sebagai referensi historis.

### ISS-001: Fix Layout Views to AdminLTE 4 Classes — P-1

**Status:** Done
**Created:** 2026-07-27
**Resolved:** 2026-07-27
**Estimate:** M
**Depends on:** DESIGN.md (done)

### Description

Views still use AdminLTE 3 class names. Must be updated to match `DESIGN.md`.

### Files to fix

| File | Current (wrong) | Target (correct) |
|------|----------------|------------------|
| `app/Views/layout/header.php` | `div.wrapper`, `nav.main-header` | `div.app-wrapper`, `nav.app-header` |
| `app/Views/layout/sidebar.php` | `aside.main-sidebar`, `div.sidebar`, `ul.nav.nav-pills.nav-sidebar`, brand link langsung di aside | `aside.app-sidebar`, `div.sidebar-wrapper`, `ul.nav.sidebar-menu`, bungkus brand di `div.sidebar-brand` |
| `app/Views/layout/footer.php` | `footer.main-footer`, nutup `content-wrapper` | `footer.app-footer`, nutup `app-main` |
| `app/Views/dashboard/index.php` | `content-header`, `section.content` | `app-content-header`, `div.app-content` |
| `app/Views/login/login.php` | `content-header`, `section.content` (if any) | `app-content-header`, `div.app-content` |

### Acceptance Criteria

- [x] All 5 files use AdminLTE 4 class names
- [x] Page renders with correct grid layout (no visual breakage)
- [x] Sidebar toggle still works
- [x] `npm run build` passes

### Subtasks

- [x] Fix `header.php` layout classes
- [x] Fix `sidebar.php` layout classes + sidebar menu classes
- [x] Fix `footer.php` layout classes
- [x] Fix `dashboard/index.php` content classes
- [x] Fix `login/login.php` content classes
- [x] Verify: `npm run build` still passes
- [x] Verify: all pages render with correct AdminLTE 4 layout

---

### ISS-002: Bootstrap 5 Utility Audit — P-1

**Status:** Done
**Created:** 2026-07-27
**Resolved:** 2026-07-27
**Estimate:** S
**Depends on:** ISS-001 (views may have cascading changes)

### Description

After AdminLTE 3→4 migration, Bootstrap 4 utilities may still remain.

### Acceptance Criteria

- [x] Zero matches for old Bootstrap 4 utilities in `app/Views/`
- [x] All `data-*` attributes use `data-bs-*`

### What to check

| Old (Bootstrap 4) | New (Bootstrap 5) | Locations to search |
|-------------------|-------------------|-------------------|
| `data-toggle` | `data-bs-toggle` | All view files |
| `data-target` | `data-bs-target` | All view files |
| `data-dismiss` | `data-bs-dismiss` | All view files |
| `ml-*`, `mr-*` | `ms-*`, `me-*` | All view files |
| `pl-*`, `pr-*` | `ps-*`, `pe-*` | All view files |
| `float-left`, `float-right` | `float-start`, `float-end` | All view files |
| `text-left`, `text-right` | `text-start`, `text-end` | All view files |
| `font-weight-*` | `fw-*` | All view files |
| `font-italic` | `fst-italic` | All view files |

### Subtasks

- [ ] `grep -rn 'data-toggle\|data-target\|data-dismiss' app/Views/` — fix all matches
- [ ] `grep -rn 'ml-\|mr-\|pl-\|pr-' app/Views/` — fix all matches
- [ ] `grep -rn 'float-left\|float-right\|text-left\|text-right' app/Views/` — fix all matches
- [ ] `grep -rn 'font-weight-\|font-italic' app/Views/` — fix all matches

---

### ISS-009: Dashboard Tidak Mengirim `$username` ke Header View — P-1

**Status:** Done
**Created:** 2026-07-27
**Resolved:** 2026-07-27
**Estimate:** S
**Depends on:** —

### Description

`Dashboard::index()` concatenates 4 partial views but only passes `$username` to `dashboard/index`. The `header.php` partial also uses `$username` (line 35), causing "Undefined variable $username" on every dashboard page load after login.

### Root Cause

`app/Controllers/Dashboard.php:11`:
```php
return view('layout/header')  // ← $username not passed
    . view('layout/sidebar')
    . view('dashboard/index', ['username' => $username])
    . view('layout/footer');
```

### Files to fix

| File | Line | What |
|------|------|------|
| `app/Controllers/Dashboard.php` | 11 | Add `['username' => $username]` to header view call |

### Acceptance Criteria

- [x] `header.php` receives `$username` and renders the username in the navbar
- [x] Login flow completes without "Undefined variable" error
- [x] `vendor/bin/phpunit` passes with all tests green
- [x] `php -l` passes on `Dashboard.php`

---

### ISS-003: CODING_STANDARDS.md + Tooling Config — P-2

**Status:** Done
**Created:** 2026-07-27
**Estimate:** M
**Depends on:** DESIGN.md (done)

### Description

Create coding standards document and tooling configuration files.

### Acceptance Criteria

- [x] `CODING_STANDARDS.md` exists and covers PHP, JS, Views, CSS conventions
- [x] `.php-cs-fixer.dist.php` exists with PSR-12 ruleset
- [x] `.editorconfig` exists with consistent indent, charset, line endings

---

### ISS-004: CHANGELOG.md — P-2

**Status:** Done
**Created:** 2026-07-27
**Estimate:** S
**Depends on:** —

### Description

Create release history following Keep a Changelog format.

### Acceptance Criteria

- [x] `CHANGELOG.md` exists with `[Unreleased]` section
- [x] Entry mencakup: Login Feature + AdminLTE Migration
- [x] Agent knows to update `[Unreleased]` every commit

---

### ISS-005: .github/PULL_REQUEST_TEMPLATE.md — P-2

**Status:** Done
**Created:** 2026-07-27
**Estimate:** S
**Depends on:** —

### Description

Create PR template with self-review checklist.

### Acceptance Criteria

- [x] Template exists at `.github/PULL_REQUEST_TEMPLATE.md`
- [x] Checklist includes: no debug code, input validation, `esc()`, no duplication, `php -l`, `npm run build`

### Checklist items

- No debug code (`dd()`, `var_dump()`, `console.log()`)
- Input validation + CSRF on all POST forms
- `esc()` on all output
- No duplicate code
- `php -l` passed on all PHP files
- `npm run build` passed (if views changed)

---

### ISS-006: .github/workflows/ci.yml — P-2

**Status:** Done
**Created:** 2026-07-27
**Estimate:** M
**Depends on:** ISS-004, ISS-005 (directory structure)

### Description

Create CI workflow for automated safety checks.

### Acceptance Criteria

- [x] CI runs on push & pull_request to main
- [x] Jobs: Setup PHP 8.2 + Node 20
- [x] Steps: `composer install`, `npm ci`, `npm run build`, `php -l`, `vendor/bin/phpunit`

---

### ISS-007: Unit Tests — Auth & NeoFeeder — P-3

**Status:** Done
**Created:** 2026-07-27
**Estimate:** L
**Depends on:** —

### Description

Create initial test suite for agent confidence checking.

### Acceptance Criteria

- [x] `AuthTest` — login success, login failed (mocked NeoFeeder), isLoggedIn, logout
- [x] `NeoFeederTest` — getToken parsing, error handling, connection failure
- [x] `AuthFilterTest` — redirect when not logged in, passthrough when logged in
- [x] `vendor/bin/phpunit` passes with all tests green

### Framework

CIUnitTestCase + mocking

---

## ISS-008: RBAC — Future Scope — P-3

**Status:** Deferred
**Created:** 2026-07-27
**Estimate:** XL
**Depends on:** ISS-001 (stable layout foundation)

### Description

Role-Based Access Control was removed from active project scope during Login Feature planning. Revisit when authorization requirements are defined.

### Acceptance Criteria

- TBD — no specification yet

---

## Known Issues

| Issue | Notes | Tracked In |
|-------|-------|------------|
| Login page may need responsive review | Verify after ISS-001 fix | ISS-001 |
| Asset build script uses `npm run build` | Verify it copies all assets correctly | — |
| No custom CSS file yet | Bootstrap utilities only — revisit when layout needs diverge | — |
