# BASE Architecture

## System Overview

BASE is a CodeIgniter 4 web application that serves as a Web Service Client for Neo Feeder (PDDIKTI). Authentication is session-based using Neo Feeder as the external identity provider — no local user database is used. The UI uses AdminLTE 4 (Bootstrap 5) with vanilla JavaScript.

> **Related documents:** See `CONTRIBUTING.md` for coding conventions and the decision framework.

---

## Tech Stack

| Layer | Technology | Version | Role |
|-------|-----------|---------|------|
| Framework | CodeIgniter 4 | ^4.7 | MVC, routing, filters, HTTP client |
| Identity Provider | Neo Feeder WS API | — | Authentication & token validation |
| UI Template | AdminLTE 4 | ^4.0 | Dashboard layout, sidebar, navbar |
| CSS Framework | Bootstrap 5 | 5.3.x | Components, grid, utilities (transitive via AdminLTE 4) |
| Icons | Font Awesome 7 | ^7.3 | UI icons |
| JS Runtime | Vanilla JavaScript | — | AdminLTE 4 native, no jQuery |
| Session | CI4 FileHandler | — | Auth state storage |
| HTTP Client | CI4 CURLRequest | — | Neo Feeder API communication |
| Encryption | CI4 Encryption Service | — | Prior-auth cookie signing |

---

## Request Lifecycle

```mermaid
sequenceDiagram
    participant Browser
    participant CI4 as CodeIgniter 4 Engine
    participant ReqFilters as Required Filters
    participant GlobFilters as Global Filters
    participant CSRF
    participant Router
    participant Controller
    participant Service
    participant View
    participant NeoFeeder as Neo Feeder API

    Browser->>CI4: HTTP Request
    CI4->>ReqFilters: forcehttps, pagecache
    ReqFilters->>GlobFilters: auth filter (except login*)
    GlobFilters->>CSRF: csrf filter
    CSRF->>Router: match route
    Router->>Controller: call method
    Controller->>Service: business logic (Auth, NeoFeeder)
    Service->>NeoFeeder: optional API call
    NeoFeeder-->>Service: JSON response
    Service-->>Controller: result
    Controller->>View: render with data
    View-->>Browser: HTML response
```

Filter ordering: **Required before** → **Global before** → Route → Controller → View → **Global after** → **Required after**

- **Required filters** (`$routes->setFilter()`) run on every request regardless of route, wrapped outside global filters.
- **Global filters** (defined in `Config\Filters::$globals`) run on all routes except those explicitly excluded.
- **Route filters** (defined per-route in `$routes->get()` options) run only on specific routes.

---

## Routes

| Method | URI | Handler | Auth Filter | CSRF |
|--------|-----|---------|-------------|------|
| GET | `/` | `Home::index` | Protected | — |
| GET | `/login` | `Login::index` | Whitelisted | — |
| POST | `/login` | `Login::attemptLogin` | Whitelisted | Enabled |
| POST | `/logout` | `Login::logout` | Protected | Enabled |
| GET | `/dashboard` | `Dashboard::index` | Protected | — |
| GET | `/profil-pt` | `ProfilPT::index` | Protected | — |

---

## Key Classes & Dependencies

| Class | File | Responsibility | Dependencies |
|-------|------|---------------|--------------|
| `BaseController` | `Controllers/BaseController.php` | Shared controller setup (session, helpers) | — |
| `Home` | `Controllers/Home.php` | Root redirect: `/login` (anonymous) or `/dashboard` (authenticated) | `Auth` service |
| `Login` | `Controllers/Login.php` | Login form, login attempt, logout | `Auth` service |
| `Dashboard` | `Controllers/Dashboard.php` | Protected landing page | `Auth` service, 4 views |
| `ProfilPT` | `Controllers/ProfilPT.php` | PT profile page: formats `GetProfilPT` data (SK date to Indonesian, website URL normalization) | `Auth`, `NeoFeeder` services, view `profil_pt/index` |
| `AuthFilter` | `Filters/AuthFilter.php` | Route protection middleware | `Auth` service, CI4 Session |
| `Auth` | `Libraries/Auth.php` | Auth logic: login, logout, validate, caching | `NeoFeeder`, `Session`, `EncrypterInterface` |
| `NeoFeeder` | `Libraries/NeoFeeder.php` | HTTP layer for Neo Feeder API | `NeoFeederConfig`, `CURLRequest` |
| `NeoFeeder` (Config) | `Config/NeoFeeder.php` | API base URL, timeouts, TTL | — |

### Dependency Injection Chain

```
Services::auth()
  └─> Auth(NeoFeeder, Session, EncrypterInterface)

Services::neoFeeder()
  └─> NeoFeeder(NeoFeederConfig, CURLRequest)
```

Both registered as singletons in `app/Config/Services.php`.

---

## Authentication Flow

```mermaid
sequenceDiagram
    participant Browser
    participant LoginCtrl as LoginController
    participant AuthSvc as Auth Service
    participant NeoFdrSvc as NeoFeeder Service
    participant NeoFdrAPI as Neo Feeder API
    participant Session

    Browser->>LoginCtrl: POST /login (email, password)
    LoginCtrl->>AuthSvc: login(email, password)

    AuthSvc->>AuthSvc: validate non-empty
    AuthSvc->>NeoFdrSvc: getToken(email, password)
    NeoFdrSvc->>NeoFdrAPI: POST GetToken
    NeoFdrAPI-->>NeoFdrSvc: {error_code, data.token?}
    NeoFdrSvc-->>AuthSvc: parsed array

    alt error_code === 0 with valid token
        AuthSvc->>Session: regenerate + set auth.{token, username, lastValidatedAt}
        AuthSvc->>AuthSvc: set prior_auth cookie (24h, httpOnly)
        AuthSvc-->>LoginCtrl: true
        LoginCtrl-->>Browser: redirect /dashboard
    else error_code === -1 (connection failure)
        AuthSvc-->>LoginCtrl: false + "unable to connect"
        LoginCtrl-->>Browser: redirect /login with error
    else any other failure
        AuthSvc-->>LoginCtrl: false + "check credentials"
        LoginCtrl-->>Browser: redirect /login with error
    end
```

### Token Validation (Auth Filter → isLoggedIn → validateToken)

```
isLoggedIn():
  1. Check session auth.token exists → if null, return false
  2. Check auth.lastValidatedAt + TTL (300s) > now → if valid, return true
  3. Otherwise call validateToken()

validateToken():
  GET /dashboard → AuthFilter → Auth::isLoggedIn() → [cache miss] → NeoFeeder::getProfilPT()
  
  error_code=0    → update lastValidatedAt, allow access
  error_code=100  → clear auth session, redirect /login with "session expired"
  error_code=-1   → fallback to cached result if within TTL, else deny (keep session)
  error_code=-2   → clear auth session, redirect /login with "session expired"
  other errors    → deny access (keep session intact)
```

### Prior-Auth Cookie (Session Timeout Detection)

When CI4 session expires but the prior-auth cookie still exists, the Auth Filter detects this and shows "Your session has expired" instead of a silent redirect. This distinguishes "never logged in" from "session timed out."

---

## Data Model

### Session Schema

```
auth (namespace)
├── username: string        # User email from login form
├── token: string           # Neo Feeder API token
└── lastValidatedAt: int    # Unix timestamp of last GetProfilPT success
```

### Cookie

```
prior_auth: base64(encrypt(username | hmac(token)))
    ├── expires: +24 hours
    ├── httpOnly: true
    ├── sameSite: Lax
    └── secure: true (production)
```

---

## Environment Variables

Key `.env` variables used by the application:

| Variable | Type | Used By | Purpose |
|----------|------|---------|---------|
| `app.baseURL` | string | CI4 Router | Base URL for `base_url()` helper |
| `encryption.key` | hex string | CI4 Encryption Service | Cookie encryption (32-byte hex) |
| `neofeeder.apiBaseUrl` | string | `Config/NeoFeeder.php` | Neo Feeder API base URL (required) |
| `neofeeder.connectionTimeout` | int | `Config/NeoFeeder.php` | Connection timeout (seconds) |
| `neofeeder.requestTimeout` | int | `Config/NeoFeeder.php` | HTTP request timeout (seconds) |
| `neofeeder.validationTTL` | int | `Config/NeoFeeder.php` | Token validation cache TTL (seconds) |

Set these in `.env` (copy from `env` template). Never commit `.env`.

---

## Asset Pipeline

```
npm install
  └─> node_modules/
        ├── admin-lte/dist/
        ├── bootstrap/dist/
        └── @fortawesome/fontawesome-free/

npm run build  (scripts/build-assets.js)
  └─> public/
        ├── adminlte/css/adminlte.min.css
        ├── adminlte/js/adminlte.min.js
        ├── bootstrap/css/bootstrap.min.css
        ├── bootstrap/js/bootstrap.bundle.min.js
        ├── fontawesome/css/all.min.css
        └── fontawesome/webfonts/*.woff2
```

All assets are pre-compiled (no bundler). The build script copies files from `node_modules/` to `public/`.

---

## Directory Structure (Key Directories)

```
app/
├── Config/          # NeoFeeder, Filters, Routes, Services
├── Controllers/     # Login, Dashboard, ProfilPT, Home, BaseController
├── Filters/         # AuthFilter (route protection)
├── Libraries/       # Auth, NeoFeeder (service layer)
└── Views/           # login/ (standalone), layout/ (header,sidebar,footer), dashboard/, profil_pt/
public/              # index.php + built assets
├── adminlte/        # adminlte.min.css, adminlte.min.js
├── bootstrap/       # bootstrap.min.css, bootstrap.bundle.min.js
└── fontawesome/     # all.min.css, webfonts/
```
