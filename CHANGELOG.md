# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

<!--
Agent instructions:
- After every feat:/fix:/refactor:/perf:/security: commit, add an entry under [Unreleased] in the correct category (Added/Changed/Fixed/Removed/Security).
- Skip chore:, docs:, test: commits unless they introduce user-facing changes.
- Before a release, move [Unreleased] into a new version header with today's date. After moving, recreate a blank [Unreleased] section with empty category placeholders:
```markdown
## [Unreleased]

### Added
_None yet._

### Changed
_None yet._

### Fixed
_None yet._

### Removed
_None yet._

### Security
_None yet._
```
-->

## [Unreleased]

### Added

- **Profil PT page:** dedicated page (`/profil-pt`) displaying institution profile data from NeoFeeder API — identity, contact, address, and legalitas in two AdminLTE cards; sidebar navigation item added
- **Route:** `GET /profil-pt` → `ProfilPT::index` with auth+csrf filters
- **Neo Feeder menu pages:** read-only pages for `Daftar Mahasiswa` (`/mahasiswa`), `Aktivitas Kuliah Mahasiswa` (`/aktivitas-kuliah`), and `Daftar Mahasiswa Lulus/Dropout` (`/mahasiswa-lulus-do`). Each calls the corresponding NeoFeeder `Get` API through the service layer with `filter`/`limit`/`offset` pagination — a direct browsing surface for the graduation-flow verification (ENH-014) — #51
- **Graduation wizard resume store:** `Libraries/WizardProgress` (CI4 Cache-backed, survives auth-session expiry) plus `Auth` resume-token cookie helpers (`setWizardResumeCookie`/`getWizardResumeToken`/`clearWizardResumeCookie`). Enables the PISN graduation wizard (ENH-013) to resume mid-batch after re-login, with no local database — ENH-016, #55
- **PISN graduation wizard:** `Graduation` controller + views (`graduation/upload`, `graduation/wizard`, `graduation/guidance`) with Excel upload (`phpoffice/phpspreadsheet`), sequential per-student manual verification (identity → academic → PISN eligibility → graduation input) advanced with a Next button, resume across auth-session expiry via the ENH-016 store, and submission to NeoFeeder via `InsertMahasiswaLulusDO` (No Ijazah = "-"). PISN live API deferred — `PisnService` scaffolding seam only — ENH-013, #53
- **Excel support:** added `phpoffice/phpspreadsheet` dependency for graduation candidate upload — ENH-013, #53
- **Neo Feeder CRUD (Biodata & Perkuliahan):** edit/delete actions on `Daftar Mahasiswa` (`/mahasiswa/edit`, `/mahasiswa/delete`) and `Aktivitas Kuliah Mahasiswa` (`/aktivitas-kuliah/edit`, `/aktivitas-kuliah/delete`) wired to NeoFeeder `Insert`/`Update`/`Delete` WS actions via the service layer. Forms are generated from the returned row columns; composite primary key (`id_registrasi_mahasiswa`+`id_semester`) handled for Perkuliahan. `Mahasiswa Lulus/DO` mutation endpoints are not documented in the available guide, so no CRUD was added there — ENH-015, #52
- **PISN graduation Excel template:** downloadable `.xlsx` template with headers (nim, nama, jenis_keluar, tgl_keluar, periode_keluar, ipk) and an example row on `graduation/upload` page — ENH-018, #70
- **PISN graduation pre-submit preview:** consolidated preview page (`/graduation/preview`) listing all verified students' graduation data before Neo Feeder submission, with explicit "Kirim ke Neo Feeder" confirmation — ENH-019, #64
- **PISN graduation cancel session:** "Batalkan sesi" button on the upload page that clears the wizard progress cache and resume cookie, returning to a fresh upload form without waiting for the 24h TTL — ENH-023, #76
- **PISN graduation transcript completeness check:** the graduation wizard loads each student's transcript (GetTranskripMahasiswa) and shows a "Kelengkapan Transkrip" card with a green "Lengkap" / red "Belum lengkap" badge when the thesis/skripsi grade is missing (soft warning, Next stays enabled) — ENH-020, #67
- **Per-student detail page (ENH-021, #66):** new `GET /mahasiswa/detail/(:any)` → `Mahasiswa::detail()` renders a single student's full biodata record (all columns from `GetBiodataMahasiswa`) read-only, with a "Detail" button added to each row in the Mahasiswa list. Reuses the `fetchBiodata($id)` single-record fetch shared with `edit()`.
- **Pagination & UI/UX refinements on menu pages (ENH-022, #68):** the three NeoFeeder menu pages (Mahasiswa, Aktivitas Kuliah, Mahasiswa Lulus/DO) gained a page-size selector (10/20/50/100), a "Halaman X dari Y" indicator with total count (via `GetCount*` endpoints), First/Prev/Next/Last links, and accessible `<nav>` markup; list headers now use human-readable column labels. Also fixes a latent bug where typed filters were stripped before reaching the API (filter is now built as a SQL-string).
- **Fill missing thesis grade (ENH-010, #110):** the Excel template gains an optional `nilai_skripsi` letter-grade column; at submission (`finish()`) the graduation wizard fills a thesis/skripsi course that is registered but still has no grade (via `UpdateNilaiPerkuliahanKelas`, composite key `id_registrasi_mahasiswa` + `id_kelas_kuliah` from the cloud transcript row) using the Excel value — only when the grade is currently missing and a value was provided. Card 3 shows the value and tags the row "(akan diisi)".

### Changed

- **PISN graduation wizard:** hides every UUID-valued column in the identity and academic verification tables (detected by UUID pattern, including `id_registrasi_mahasiswa`, `id_mahasiswa`, `id_aktivitas_kuliah`, `id_perguruan_tinggi`, `id_sms`, `id_agama`, `id_prodi`, `id_status_mahasiswa`, `id_periode`, `id_periode_keluar`); the `id_semester` code and human-readable columns remain visible — ENH-001, #85
- **Repo restructured to professional GitHub standard:** removed agent-only docs (`AGENTS.md`, `OPEN_ISSUES.md`, `DESIGN.md`, `CODING_STANDARDS.md`, `.memory/`); archived Sprint 1 issues to `docs/archived/`; added `CODE_OF_CONDUCT.md` (Contributor Covenant 2.1), `SECURITY.md` (supported versions + disclosure policy), GitHub issue templates (bug report, feature request), and a release notes template (`.github/RELEASE_NOTES_TEMPLATE.md`); `CONTRIBUTING.md` now holds coding conventions + GitHub Flow workflow; LICENSE adds project copyright; all documentation translated to English
- **Documentation shrinkage for model context:** `DESIGN.md` 1138→473 lines (removed Bootstrap 5 boilerplate, kept project-specific patterns only); `CODING_STANDARDS.md` 537→287 lines (trimmed general PHP knowledge); `OPEN_ISSUES.md` 295→54 lines (archived 9 done Sprint 1 items to `docs/archived/SPRINT1_ISSUES.md`)
- **Documentation restructure:** `AGENTS.md` split into Golden Path (must-survive compression) + Reference sections; `CONTRIBUTING.md` deduplicated to human-focused content — lifecycle removed (delegated to AGENTS.md)
- **Sidebar navigation:** added auto-detection of active page via `active` CSS class on current route
- **Dashboard version:** replaced hardcoded `v0.1.0` with dynamic value from `config('AppVersion')->version`
- **Release process:** added step to update `app/Config/AppVersion.php` when releasing a new version
- **Profil PT page:** redesigned layout with visual hierarchy — hero card `card-outline-primary` (name in `display-6` + accreditation badge, identity & legalitas info in `d-flex gap-4`), two `card-info h-100` cards side by side (Contact with clickable links + Address), `fa-fw` for aligned icons, equal-height via `d-flex flex-column` + `mt-auto`, `https://` prepended to website URL, Indonesian date format via `strtr()`, max width via `col-xxl-10`
- **Dependencies:** bumped `admin-lte` from 4.1.0 to 4.8.4 (AdminLTE 4.x line — no breaking changes, backward-compatible HTML/CSS)
- **PISN graduation wizard:** removed the unused `academic_flag` textarea and `biaya_kuliah` input; the academic table (card 2) is now inline-editable per semester — `status` as a dropdown sourced from Neo Feeder `GetStatusMahasiswa`, plus editable `ips`/`ipk` — with corrections pushed to Neo Feeder via `updatePerkuliahanMahasiswa` at submission — ENH-003, #87
- **PISN graduation wizard:** sorts the academic verification table (card 2) by `id_semester` ascending (oldest→newest), via API `order` plus a defensive `usort` fallback — ENH-002, #86
- **PISN graduation wizard:** formats the step 4 "Input Kelulusan" inputs to match Neo Feeder — `jenis_keluar` is a dropdown of PDDIKTI labels (stores the `id_jenis_keluar` code), `periode_keluar` is a semester dropdown (stores `id_semester`), `tgl_keluar` is a native date input (`YYYY-MM-DD`); the `InsertMahasiswaLulusDO` record is rebuilt to the live WS schema (`id_registrasi_mahasiswa` resolved, `id_jenis_keluar`, `tanggal_keluar`, `id_periode_keluar`, `ipk`, `nomor_ijazah`) — ENH-004, #88
- **PISN graduation wizard:** renumbered the verification step cards from (`1`, `2`, `2b`, `3`, `4`) to whole numbers (`1`, `2`, `3`, `4`, `5`) — the Kelengkapan Transkrip card is now its own step 3 — ENH-009, #103
- **PISN graduation Excel template:** reduced to the minimal columns the workflow needs — `nim`, `nama` (optional, for KTP-name validation), `tgl_keluar`, `ipk`; `jenis_keluar`/`periode_keluar` columns removed. `jenis_keluar` auto-defaults to `Lulus` (PISN graduation is always that jenis keluar) and `periode_keluar` is auto-derived from `tgl_keluar` by range-matching the Daftar Semester (Ganjil/Genap only, Pendek excluded); both remain manually overridable in the step 5 dropdowns. `tgl_keluar` date cells are now parsed robustly whether PhpSpreadsheet surfaces them as a `DateTime` object, an Excel serial number, or a plain `YYYY-MM-DD` string — ENH-005, #99
- **PISN graduation wizard:** restricts the step 2 academic table to inline-editable rows only for semesters still active in the Daftar Semester — a row is editable (status dropdown + `ips`/`ipk` inputs) only when its `id_semester` has `a_periode_aktif='1'`; all other (historical) rows render read-only (disabled, not submitted) — ENH-007, #101
- **PISN graduation wizard:** the last academic row's IPK and the step 5 graduation IPK input are now a single source sync'd two-way in realtime (typing in either updates the other; the Excel IPK is only the initial default). When the last (newest) academic row is still active and its IPK differs from the Excel IPK, an "Auto-update IPK & status ke Aktif" button stages the Excel IPK and the Aktif status (resolved from the status dropdown, not hardcoded) for that row; when the last row is inactive, only an informational warning is shown. The staged value is captured by the existing `stepPost` and pushed at `finish()` — ENH-006, #100
- **PISN graduation wizard:** every verification step now has a checkbox that must be checked before advancing; all four are required by `stepPost`. Step 1 (`identity_ok`) auto-checks when the Excel `nama` matches the PDDIKTI registered name; step 2 (`academic_ok`) auto-checks when the last active academic row's IPK equals the Excel IPK AND its status is Aktif (recomputed in realtime in the browser, including after the ENH-006 Auto-update button); step 3 (`transcript_ok`) auto-checks when a thesis/skripsi MK exists with a grade AND `choosed:true`; step 4 (`pisn_ok`) is manual. Every checkbox remains manually togglable regardless of the auto-check state — ENH-008, #102

### Fixed

- **PISN graduation wizard validation errors not shown:** `step()` never read the `graduation_error` flash set by `stepPost()`, so a failed verification (missing checkbox, bad graduation input) silently re-rendered the step with no message; API-error assignments also overwrote it. `step()` now initializes `$error` from the `graduation_error` flash and the API-error branches guard with `empty($error)`, so validation failures now display their aggregated messages on the step — ENH-008, #102

- **Graduation wizard transcript completeness (BUG-001, #89):** the "Kelengkapan Transkrip" check is now sourced from the Neo Feeder "Cek Transkrip Mahasiswa" menu data (cloud REST `/ws/transkrip/nilai_mahasiswa`, using BASE's existing WS `act` token as Bearer — no separate cloud login) instead of `GetTranskripMahasiswa`. Completeness now requires the thesis/skripsi course to exist with a non-empty grade; the per-course `choosed` transcript-inclusion marker is shown as status detail (badge "Nilai ada · masuk transkrip"/"belum masuk transkrip"/"Tanpa nilai") but no longer gates the result. New `NeoFeeder::getCekTranskripMahasiswa()` + `cloudGet()` helper; card 2b renders the detected thesis course (MK, kode, semester, nilai, status).
- **FA6 icon consistency:** `header.php` logout button and `DESIGN.md` reference still used FA5 deprecated names (`fa-sign-out-alt` → `fa-right-from-bracket`, `fa-tachometer-alt` → `fa-gauge-high`) — aligned to canonical FA6 names to match `sidebar.php`
- **Sidebar active state:** fixed route detection — `getPath()` returned `index.php/dashboard` instead of `dashboard`; now uses `current_url()` with `basename()` which handles both URL formats

- **Windows php-cs-fixer CRLF false positives:** resolved by repo-wide LF normalization (`.editorconfig` `end_of_line = lf`, `.gitattributes` `* text=auto eol=lf`, `git add --renormalize .`); CI `build` now asserts LF line endings / no BOM on `*.md` — #48
- **Neo Feeder edit/delete loader (BUG-001, #65):** the edit route handlers were defined without a `$1`/`$2` back-reference, so CodeIgniter 4.7.4 discarded the route parameter and `$id` was always `null` — the loader filtered by `null` and `GetBiodataMahasiswa`/`GetDetailPerkuliahanMahasiswa` returned empty, showing "Data tidak ditemukan." Fixed by adding `$1`/`$2` back-references to the route handlers (`Mahasiswa::edit/$1`, `AktivitasKuliah::edit/$1/$2`, etc.) and switching the loaders to the dedicated per-record endpoints with the SQL-string `filter` (`id_mahasiswa='…'`) — Edit/Delete now open the correct record.
- **Graduation wizard filter format (BUG-002, #75):** the `Graduation::step()` method passed `filter` as an array `['nim' => ...]` to `getListMahasiswa()` and `getAktivitasKuliahMahasiswa()`, but the NeoFeeder API expects a SQL WHERE-string. The API silently returned empty data, causing "Gagal memuat data mahasiswa." on the first wizard step. Fixed by sanitizing the NIM and passing SQL-string filter `nim='...'` to both calls.

### Removed
_None yet._

### Security
_None yet._

## [0.2.0] - 2026-07-28

### Added

- **Dashboard content:** replaced empty welcome card with professional stat cards (active user, NeoFeeder status, session status, app version)
- **Login page:** added "Remember me" checkbox, password visibility toggle, and field-level validation feedback
- **Error pages:** themed 404 and 500 pages with Bootstrap branding, app icon, and "Back to Dashboard" link
- **Custom assets:** created `public/css/app.css` and `public/js/app.js` for project-specific styles and scripts
- **Favicon:** linked `favicon.ico` in `<head>` of both header and login layouts

### Changed

- **Header navbar:** replaced user profile dropdown with inline "Signed in as: $username" text and a direct Logout button (hidden on mobile <768px, finger-friendly padding)
- **Login field:** changed `type="email"` to `type="text"` with placeholder "Email or Username" to match the auth service (accepts both)
- **Login form icon:** changed `fa-envelope` to `fa-at` for dual-purpose email/username field
- **Login accessibility:** added `visually-hidden` labels for username and password inputs, `aria-label` on password toggle
- **Dynamic page title:** header now supports `$title` variable for per-page `<title>` (defaults to app name)
- **Sidebar username:** now accepts `$username` from controller (with session fallback) for consistency with header and dashboard
- **Footer copyright:** shortened from full product name to "BASE"
- **Layout views:** migrated header, sidebar, footer, and dashboard views from AdminLTE 3 to AdminLTE 4 classes (`app-wrapper`, `app-sidebar`, `app-main`, etc.)
- **Font Awesome 6 icons:** updated `fa-sign-out-alt` → `fa-right-from-bracket`, `fa-tachometer-alt` → `fa-gauge-high` to use canonical FA6 names
- **Sidebar accessibility:** added `aria-label="Toggle sidebar"` on the hamburger menu link
- **Username overflow:** added `text-truncate` on sidebar and dashboard username, CSS rule for navbar dropdown truncation
- **Code style:** applied `php-cs-fixer` rules across `app/` and `tests/` — fixed import ordering, octal notation, anonymous class syntax
- **Login page:** aligned markup with AdminLTE 4 — `<main class="login-box">`, `<h1 class="login-logo">`, removed subtitle
- **Header user dropdown:** migrated to AdminLTE 4 `user-menu` pattern — icon-only trigger, username only inside dropdown `user-header`, `user-footer` with sign out
- **Sidebar:** removed redundant user-panel (username moved to header dropdown)
- **Footer:** updated copyright text to match AdminLTE 4 preview
- **Dashboard small-box:** replaced `$username` heading with "Session" label for shorter card text
- **Login button:** replaced checkbox+button row with `d-grid gap-2` full-width button (matching AdminLTE 4 login page)

### Fixed

- **Dashboard small-box icon:** fixed icon class from `.icon` to `.small-box-icon` — AdminLTE 4 CSS targets `.small-box-icon` for absolute positioning (top-right, 70px, opacity .15) — icon was previously rendering at default inline position (left-bottom, small size)
- **Google Fonts:** updated from `Source+Sans+Pro` (AdminLTE 3) to `Source+Sans+3` — matches AdminLTE 4 default `--bs-font-sans-serif`

- **Password toggle:** broken show/hide button — JS queried `<i>` but HTML used `<span>`, causing TypeError
- **Login error styling:** removed blanket `is-invalid` on both fields; flash message is now the sole error feedback
- **Null safety:** added `$username ?? session('auth.username')` fallback in header and dashboard views
- **Footer escaping:** `date('Y')` now wrapped with `esc()`
- **Dashboard heading:** changed `<h1>` to `<h3>` to match DESIGN.md spec
- **Dashboard labels:** replaced hardcoded misleading status text ("Terhubung", "Aktif") with neutral labels
- **Sidebar username:** replaced dead `<a href="#">` with `<span>` element
- **Dashboard header:** pass `$username` variable to header view to eliminate "Undefined variable" error
- **AuthTest:** align `testLogoutClearsAuthSession` expectation with actual `destroy()` call

### Removed

- **welcome_message.php:** deleted unused default CI4 welcome page (Home controller always redirects)
- **Login page:** removed dead "Remember me" checkbox (backend never processes it) and hidden "Forgot password?" link
- **app.css:** removed unused navbar dropdown truncation rule (no longer needed after header dropdown restyle)

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

[unreleased]: https://github.com/ffooll-bit/BASE/compare/v0.2.0...HEAD
[0.2.0]: https://github.com/ffooll-bit/BASE/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/ffooll-bit/BASE/compare/v0.0.0...v0.1.0
