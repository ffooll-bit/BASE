# Open Issues / Backlog

Priority levels: **P-1** (urgent), **P-2** (important), **P-3** (nice to have).

---

## ISS-001: Fix Layout Views to AdminLTE 4 Classes — P-1

**Status:** Open
**Created:** 2026-07-27
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

- [ ] All 5 files use AdminLTE 4 class names
- [ ] Page renders with correct grid layout (no visual breakage)
- [ ] Sidebar toggle still works
- [ ] `npm run build` passes

### Subtasks

- [ ] Fix `header.php` layout classes
- [ ] Fix `sidebar.php` layout classes + sidebar menu classes
- [ ] Fix `footer.php` layout classes
- [ ] Fix `dashboard/index.php` content classes
- [ ] Fix `login/login.php` content classes
- [ ] Verify: `npm run build` still passes
- [ ] Verify: all pages render with correct AdminLTE 4 layout

---

## ISS-002: Bootstrap 5 Utility Audit — P-1

**Status:** Open
**Created:** 2026-07-27
**Estimate:** S
**Depends on:** ISS-001 (views may have cascading changes)

### Description

After AdminLTE 3→4 migration, Bootstrap 4 utilities may still remain.

### Acceptance Criteria

- [ ] Zero matches for old Bootstrap 4 utilities in `app/Views/`
- [ ] All `data-*` attributes use `data-bs-*`

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

## ISS-003: CODING_STANDARDS.md + Tooling Config — P-2

**Status:** Open
**Created:** 2026-07-27
**Estimate:** M
**Depends on:** DESIGN.md (done)

### Description

Create coding standards document and tooling configuration files.

### Acceptance Criteria

- [ ] `CODING_STANDARDS.md` exists and covers PHP, JS, Views, CSS conventions
- [ ] `.php-cs-fixer.dist.php` exists with PSR-12 ruleset
- [ ] `.editorconfig` exists with consistent indent, charset, line endings

### Output

- `CODING_STANDARDS.md` — PHP (PSR-12, type hints, CI4 conventions), JS (vanilla, no jQuery), views (`esc()`, alternate syntax), CSS (Bootstrap utility-first, BEM for custom)
- `.php-cs-fixer.dist.php` — PHP CS Fixer config
- `.editorconfig` — indent, charset, line endings

---

## ISS-004: CHANGELOG.md — P-2

**Status:** Open
**Created:** 2026-07-27
**Estimate:** S
**Depends on:** —

### Description

Create release history following Keep a Changelog format.

### Acceptance Criteria

- [ ] `CHANGELOG.md` exists with `[Unreleased]` section
- [ ] Entry mencakup: Login Feature + AdminLTE Migration
- [ ] Agent knows to update `[Unreleased]` every commit

### Output

`CHANGELOG.md` — release history with Keep a Changelog format.

---

## ISS-005: .github/PULL_REQUEST_TEMPLATE.md — P-2

**Status:** Open
**Created:** 2026-07-27
**Estimate:** S
**Depends on:** —

### Description

Create PR template with self-review checklist.

### Acceptance Criteria

- [ ] Template exists at `.github/PULL_REQUEST_TEMPLATE.md`
- [ ] Checklist includes: no debug code, input validation, `esc()`, no duplication, `php -l`, `npm run build`

### Checklist items

- No debug code (`dd()`, `var_dump()`, `console.log()`)
- Input validation + CSRF on all POST forms
- `esc()` on all output
- No duplicate code
- `php -l` passed on all PHP files
- `npm run build` passed (if views changed)

---

## ISS-006: .github/workflows/ci.yml — P-2

**Status:** Open
**Created:** 2026-07-27
**Estimate:** M
**Depends on:** ISS-004, ISS-005 (directory structure)

### Description

Create CI workflow for automated safety checks.

### Acceptance Criteria

- [ ] CI runs on push & pull_request to main
- [ ] Jobs: Setup PHP 8.2 + Node 20
- [ ] Steps: `composer install`, `npm ci`, `npm run build`, `php -l`, `vendor/bin/phpunit`

---

## ISS-007: Unit Tests — Auth & NeoFeeder — P-3

**Status:** Open
**Created:** 2026-07-27
**Estimate:** L
**Depends on:** —

### Description

Create initial test suite for agent confidence checking.

### Acceptance Criteria

- [ ] `AuthTest` — login success, login failed (mocked NeoFeeder), isLoggedIn, logout
- [ ] `NeoFeederTest` — getToken parsing, error handling, connection failure
- [ ] `AuthFilterTest` — redirect when not logged in, passthrough when logged in
- [ ] `vendor/bin/phpunit` passes with all tests green

### Framework

CIUnitTestCase + mocking

---

## Known Issues

| Issue | Notes | Tracked In |
|-------|-------|------------|
| Login page may need responsive review | Verify after ISS-001 fix | ISS-001 |
| Asset build script uses `npm run build` | Verify it copies all assets correctly | — |
| No custom CSS file yet | Bootstrap utilities only — revisit when layout needs diverge | — |
