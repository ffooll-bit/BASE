# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!--
Agent instructions:
- After every feat:/fix:/refactor:/perf:/security: commit, add an entry under [Unreleased] in the correct category (Added/Changed/Fixed/Removed/Security).
- Skip chore:, docs:, test: commits unless they introduce user-facing changes.
- Before a release, move [Unreleased] into a new version header with today's date.
-->

## [Unreleased]

### Added

- **Dashboard content:** replaced empty welcome card with professional stat cards (active user, NeoFeeder status, session status, app version)
- **Login page:** added "Remember me" checkbox, "Forgot password?" link, password visibility toggle, and field-level validation feedback (`is-invalid` on error)
- **Error pages:** themed 404 and 500 pages with Bootstrap branding, app icon, and "Back to Dashboard" link
- **Custom assets:** created `public/css/app.css` and `public/js/app.js` for project-specific styles and scripts
- **Favicon:** linked `favicon.ico` in `<head>` of both header and login layouts

### Changed

- **Home route:** `/` now redirects to `/login` (unauthenticated) or `/dashboard` (authenticated) instead of showing the CodeIgniter default welcome page
- **Login field:** changed `type="email"` to `type="text"` with placeholder "Email or Username" to match the auth service (accepts both), added `is-invalid` class on error
- **Dynamic page title:** header now supports `$title` variable for per-page `<title>` (defaults to app name)
- **Sidebar username:** now accepts `$username` from controller (with session fallback) for consistency with header and dashboard
- **Footer copyright:** shortened from full product name to "BASE"
- **Layout views:** migrated header, sidebar, footer, and dashboard views from AdminLTE 3 to AdminLTE 4 classes (`app-wrapper`, `app-sidebar`, `app-main`, etc.)
- **Code style:** applied `php-cs-fixer` rules across `app/` and `tests/` — fixed import ordering, octal notation, anonymous class syntax

### Fixed

- **Dashboard header:** pass `$username` variable to header view to eliminate "Undefined variable" error
- **AuthTest:** align `testLogoutClearsAuthSession` expectation with actual `destroy()` call

### Removed
_None yet._

### Security
_None yet._

## [0.1.0] - 2026-07-27 - Initial Development Release

> Initial development release. Project started as a CodeIgniter 4 scaffold and evolved through Login Feature implementation and AdminLTE migration.

### Added

- **Authentication system:** NeoFeeder API client, Auth service with login/logout/session validation, AuthFilter for route protection, Login and Dashboard controllers
- **Login feature views:** login form with CSRF protection, flash message alerts, autocomplete attributes; dashboard index view
- **AdminLTE 4 foundation:** npm asset build pipeline (package.json, build script), replaced Composer-based AdminLTE 3
- **Developer documentation:** AGENTS.md (identity & rules), CONTRIBUTING.md (workflow), ARCHITECTURE.md (system blueprint), DESIGN.md (UI/UX consistency), CODING_STANDARDS.md (code style), OPEN_ISSUES.md (backlog), PLAN.md (preparation blueprint)
- **PR template:** `.github/PULL_REQUEST_TEMPLATE.md` with self-review checklist for debug code, validation, escaping
- **CI workflow:** `.github/workflows/ci.yml` with PHP 8.2, Node 20, composer install, npm ci, npm run build, php -l, phpunit
- **Project tooling:** .editorconfig (UTF-8, CRLF, 4-space indent), .php-cs-fixer.dist.php (@PSR12 + @PHP82Migration ruleset), friendsofphp/php-cs-fixer as dev dependency

### Changed

- **CodeIgniter 4:** upgraded from v4.5.3 to v4.7.4
- **PHP constraint:** raised from ^7.4 to ^8.2
- **UI framework:** migrated all views from AdminLTE 3 (Bootstrap 4) to AdminLTE 4 (Bootstrap 5): login page, layout (header, sidebar, footer), dashboard
- **Asset management:** removed AdminLTE 3 Composer dependency, added AdminLTE 4 as npm package with build script
- **README:** updated with current project state, authentication flow, API endpoints, and NeoFeeder configuration

### Fixed

- **Login security:** session fixation — regenerate session ID before storing authentication data
- **Token handling:** null guard when NeoFeeder API returns empty token
- **Hash algorithm:** corrected HMAC to use SHA256 consistently
- **Cookie security:** enabled HTTP-only flag; Secure flag configurable via `.env`
- **Bootstrap 5 migration:** replaced `btn-block` with `w-100`, removed BS4 `input-group-append` wrapper classes
- **Form UX:** added `autocomplete` attributes to username and password fields
- **AuthFilter:** removed dead whitelist route check and silent array key fallback
- **Sidebar:** removed orphan `data-bs-toggle="collapse"` attribute (no submenus exist)
- **Build script:** added error handling for npm command failures
- **Manual E2E test reports:** corrected traceability mappings between test cases and requirements (reports removed during SDD docs cleanup)

### Removed

- **AdminLTE 3:** Composer dependency removed
- **jQuery:** explicitly banned from the project
- **SDD workflow docs:** cleaned entire docs/ directory (outdated specification, plan, task documents)
- **RBAC:** deferred from active project scope

### Security

- **CSRF protection:** enabled on all POST routes via CI4 CSRF filter
- **Output escaping:** `esc()` enforced on every HTML output across all views
- **Session fixation:** session ID regenerated on every login
- **HTTP-only flag:** enabled on session cookies

[unreleased]: https://github.com/ffooll-bit/BASE/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/ffooll-bit/BASE/compare/v0.0.0...v0.1.0
