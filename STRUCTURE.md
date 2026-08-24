# Codebase Structure

## Directory Layout

```
[project-root]/
├── app/                  # CodeIgniter 4 application code (MVC)
│   ├── Config/           # Routes, Filters, Services, NeoFeeder, env-driven config
│   ├── Controllers/      # Home, Login, Dashboard, ProfilPT, BaseController
│   ├── Filters/          # AuthFilter (route protection middleware)
│   ├── Libraries/        # Auth, NeoFeeder (service layer)
│   ├── Models/           # Empty (no local DB — placeholder .gitkeep)
│   ├── Database/         # Migrations/ and Seeds/ (placeholder .gitkeep)
│   ├── Helpers/          # Custom helpers (placeholder .gitkeep)
│   ├── Language/en/      # Validation language strings
│   ├── Views/            # login/, layout/, dashboard/, profil_pt/, errors/
│   └── ThirdParty/       # Third-party code (placeholder .gitkeep)
├── public/               # Web root: index.php + pre-compiled assets
│   ├── adminlte/         # adminlte.min.css, adminlte.min.js
│   ├── bootstrap/        # bootstrap.min.css, bootstrap.bundle.min.js
│   ├── fontawesome/      # all.min.css, webfonts/
│   ├── css/app.css       # App custom styles
│   └── js/app.js         # App custom JS
├── scripts/              # Node build scripts (build-assets.js)
├── tests/                # PHPUnit unit tests (tests/unit/)
├── docs/                 # Project documentation (IMPROVEMENTS.md, archived/)
├── reports/              # Commit-validation report artifacts
├── temp/                 # Scratch files (issue docs, PR drafts)
├── build/                # PHPUnit cache & test logs
├── writable/             # CI4 runtime storage (session, cache, logs, uploads)
├── vendor/               # Composer dependencies (CI4 framework)
├── node_modules/         # npm dependencies (admin-lte, bootstrap, fontawesome)
├── .github/              # CI workflows, issue/PR templates, dependabot
├── spark                 # CI4 CLI entry point
├── composer.json         # PHP dependencies
├── package.json          # npm dependencies + build script
├── env                   # .env template (copy to .env, never commit)
├── phpunit.xml.dist      # PHPUnit configuration
└── .php-cs-fixer.dist.php # PHP coding-style fixer config
```

## Directory Purposes

**`app/`:**
- Purpose: All application code — controllers, services, filters, config, views.
- Contains: PHP classes following CI4 MVC conventions
- Key files: `app/Config/Routes.php`, `app/Config/Services.php`, `app/Config/Filters.php`, `app/Libraries/Auth.php`, `app/Libraries/NeoFeeder.php`

**`app/Config/`:**
- Purpose: Framework and app configuration classes
- Contains: CI4 config classes + app-specific `NeoFeeder.php`, `Routes.php`, `Services.php`
- Key files: `app/Config/NeoFeeder.php` (API base URL, timeouts, TTL), `app/Config/Services.php` (DI singletons)

**`app/Controllers/`:**
- Purpose: HTTP request handlers
- Contains: `Home`, `Login`, `Dashboard`, `ProfilPT`, abstract `BaseController`
- Key files: `app/Controllers/ProfilPT.php` (PT profile page)

**`app/Libraries/`:**
- Purpose: Service layer — business logic decoupled from controllers
- Contains: `Auth` (login, logout, token validation), `NeoFeeder` (HTTP client for the Neo Feeder API)
- Key files: `app/Libraries/Auth.php`, `app/Libraries/NeoFeeder.php`

**`app/Filters/`:**
- Purpose: Request middleware
- Contains: `AuthFilter` (protects all routes except `login*`)
- Key files: `app/Filters/AuthFilter.php`

**`app/Views/`:**
- Purpose: PHP view templates
- Contains: `login/` (standalone), `layout/` (header, sidebar, footer), `dashboard/`, `profil_pt/`, `errors/`
- Key files: `app/Views/layout/header.php`, `app/Views/layout/sidebar.php`, `app/Views/profil_pt/index.php`

**`public/`:**
- Purpose: Web-accessible root; only files here are served
- Contains: `index.php` front controller and pre-compiled assets (no bundler — copied from `node_modules/` by `scripts/build-assets.js`)
- Key files: `public/index.php`, `public/css/app.css`

**`tests/`:**
- Purpose: PHPUnit unit tests
- Contains: `tests/unit/Filters/AuthFilterTest.php`, `tests/unit/Libraries/AuthTest.php`, `tests/unit/Libraries/NeoFeederTest.php`
- Key files: `phpunit.xml.dist` at project root

**`scripts/`:**
- Purpose: Node build tooling
- Contains: `build-assets.js` (copies AdminLTE/Bootstrap/Font Awesome from `node_modules/` to `public/`)
- Key files: `scripts/build-assets.js`

**`writable/`:**
- Purpose: Runtime storage — sessions, cache, logs, debugbar, uploads
- Contains: `session/`, `cache/`, `logs/`, `debugbar/`, `uploads/`

**`docs/` and `reports/` and `temp/`:**
- Purpose: Project documentation (`docs/`), CI commit-validation reports (`reports/`), scratch/working files (`temp/`)
- Note: `temp/` and `reports/` are working artifacts, not source

## Key File Locations

**Entry Points:** `public/index.php`: CI4 front controller. `spark`: CI4 CLI entry point.
**Configuration:** `app/Config/Routes.php`: route definitions. `app/Config/Services.php`: DI service registration. `app/Config/Filters.php`: required/global/route filters. `app/Config/NeoFeeder.php`: API base URL, timeouts, validation TTL.
**Core Logic:** `app/Libraries/Auth.php`: authentication, token validation, prior-auth cookie. `app/Libraries/NeoFeeder.php`: Neo Feeder API HTTP layer.
**Controllers:** `app/Controllers/`: `Login.php`, `Dashboard.php`, `ProfilPT.php`, `Home.php`, abstract `BaseController.php`.
**Middleware:** `app/Filters/AuthFilter.php`: route protection.
**Tests:** `tests/unit/`: PHPUnit tests mirroring `app/Filters/` and `app/Libraries/`.
**Assets:** `public/adminlte/`, `public/bootstrap/`, `public/fontawesome/`: pre-compiled from `node_modules/` via `npm run build`.

## Naming Conventions

**Files:** StudlyCase for classes (`Auth.php`, `NeoFeeder.php`); snake_case for views (`profil_pt/index.php`, `login/login.php`); kebab-case for public URLs (`profil-pt`).
**Directories:** lowercase, snake_case for view groups (`profil_pt/`, `dashboard/`, `layout/`); StudlyCase for class directories (`Controllers`, `Libraries`, `Config`, `Filters`).
**Routes:** lowercase, hyphen-separated URIs (`/profil-pt`, `/dashboard`, `/login`).

## Where to Add New Code

**New controller:** `app/Controllers/[ControllerName].php` — extend `BaseController`, register a route in `app/Config/Routes.php`.
**New route:** add to `app/Config/Routes.php`; protected pages are covered by the global `auth` filter (whitelist `login*` in `app/Config/Filters.php`).
**New service:** `app/Libraries/[ClassName].php` — register a singleton in `app/Config/Services.php` and inject dependencies via the constructor.
**New filter:** `app/Filters/[FilterName].php` — register the alias in `app/Config/Filters.php`.
**New view:** `app/Views/[view_group]/[page].php` — reuse `layout/header`, `layout/sidebar`, `layout/footer` for protected pages; standalone views for public pages.
**New Neo Feeder API call:** add a method to `app/Libraries/NeoFeeder.php` using the private `sendRequest()` helper.
**Tests:** `tests/unit/` mirroring the class under test, e.g. `tests/unit/Libraries/[ClassName]Test.php`.
