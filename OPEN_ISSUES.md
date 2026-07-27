# Open Issues / Backlog

Priority levels: **P-1** (urgent), **P-2** (important), **P-3** (nice to have).

---

## [P-1] AdminLTE 4 Migration — Fix Layout Views

Views still use AdminLTE 3 class names. Must be updated to match `DESIGN.md`.

### Files to fix

| File | Current (wrong) | Target (correct) |
|------|----------------|------------------|
| `app/Views/layout/header.php` | `div.wrapper`, `nav.main-header` | `div.app-wrapper`, `nav.app-header` |
| `app/Views/layout/sidebar.php` | `aside.main-sidebar`, `div.sidebar`, `ul.nav.nav-pills.nav-sidebar`, brand link langsung di aside | `aside.app-sidebar`, `div.sidebar-wrapper`, `ul.nav.sidebar-menu`, bungkus brand di `div.sidebar-brand` |
| `app/Views/layout/footer.php` | `footer.main-footer`, nutup `content-wrapper` | `footer.app-footer`, nutup `app-main` |
| `app/Views/dashboard/index.php` | `content-header`, `section.content` | `app-content-header`, `div.app-content` |
| `app/Views/login/login.php` | `content-header`, `section.content` (if any) | `app-content-header`, `div.app-content` |

### Subtasks

- [ ] Fix `header.php` layout classes
- [ ] Fix `sidebar.php` layout classes + sidebar menu classes
- [ ] Fix `footer.php` layout classes
- [ ] Fix `dashboard/index.php` content classes
- [ ] Fix `login/login.php` content classes
- [ ] Verify: `npm run build` still passes
- [ ] Verify: all pages render with correct AdminLTE 4 layout

**Depends on:** DESIGN.md (done)

---

## [P-1] Bootstrap 5 Utility Audit

After AdminLTE 3→4 migration, Bootstrap 4 utilities may still remain.

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

**Depends on:** P-1 layout fix (views may have cascading changes)

---

## [P-2] CODING_STANDARDS.md + Tooling Config

Create coding standards document and tooling configuration files.

**Output:**
- `CODING_STANDARDS.md` — PHP (PSR-12, type hints, CI4 conventions), JS (vanilla, no jQuery), views (`esc()`, alternate syntax), CSS (Bootstrap utility-first, BEM for custom)
- `.php-cs-fixer.dist.php` — PHP CS Fixer config
- `.editorconfig` — indent, charset, line endings

**Depends on:** DESIGN.md finalized

---

## [P-2] CHANGELOG.md

Create release history following Keep a Changelog format.

**Output:** `CHANGELOG.md`

**Contents:**
- `[Unreleased]` section
- Entry pertama: Login Feature + AdminLTE Migration
- Agent update `[Unreleased]` every commit

---

## [P-2] .github/PULL_REQUEST_TEMPLATE.md

Create PR template with self-review checklist.

**Output:** `.github/PULL_REQUEST_TEMPLATE.md`

**Checklist items:**
- No debug code (`dd()`, `var_dump()`, `console.log()`)
- Input validation + CSRF on all POST forms
- `esc()` on all output
- No duplicate code
- `php -l` passed on all PHP files
- `npm run build` passed (if views changed)

---

## [P-2] .github/workflows/ci.yml

Create CI workflow for automated safety checks.

**Jobs:**
- Setup PHP 8.2 + Node 20
- Steps: `composer install`, `npm ci`, `npm run build`, `php -l` on changed files, `vendor/bin/phpunit`

**Trigger:** push & pull_request to main

---

## [P-3] Unit Tests — Auth & NeoFeeder

Create initial test suite for agent confidence checking.

**Minimum tests:**
- `AuthTest` — login success, login failed (mocked NeoFeeder), isLoggedIn, logout
- `NeoFeederTest` — getToken parsing, error handling, connection failure
- `AuthFilterTest` — redirect when not logged in, passthrough when logged in

**Framework:** CIUnitTestCase + mocking

---

## Known Issues

| Issue | Notes |
|-------|-------|
| AdminLTE 4 class names in views are still v3 | Tracked in P-1 |
| Login page may need responsive review | Verify after P-1 fix |
| No custom CSS file yet | Bootstrap utilities only — revisit if layout needs diverge |
| No JavaScript file yet | Vanilla JS only — revisit when interactive features land |
| No unit tests exist | Tracked in P-3 test suite task |
| Asset build script location | Verify `npm run build` copies all assets correctly |
