# BASE (Bongaya Advanced Services Engine)

[![CI](https://github.com/ffooll-bit/BASE/actions/workflows/ci.yml/badge.svg)](https://github.com/ffooll-bit/BASE/actions/workflows/ci.yml) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## About

BASE is a web application built on CodeIgniter 4. Its primary purpose is to act as a **Web Service Client** for **Neo Feeder** — the application that synchronizes data with the PDDIKTI database.

Currently, BASE acts as a **client** of the Neo Feeder web service. Going forward, the application will be developed to integrate with other applications within STIEM Bongaya.

The name **BASE** (Bongaya Advanced Services Engine) reflects that the application will serve as the **foundation** for integrating other systems.

## Development Status

Two main features are complete:

1. **Login / Authentication** - Implemented and verified (10/10 E2E passed). The login system uses the Neo Feeder API (`GetToken`) for authentication, with session-based auth via CodeIgniter 4.
2. **AdminLTE 3 → AdminLTE 4 Migration** - The UI layer migrated from AdminLTE 3.2 (Bootstrap 4, jQuery) to AdminLTE 4 (Bootstrap 5, vanilla JS, Font Awesome 7).

## Technology Stack

| Component | Technology | Version |
|-----------|------------|---------|
| Framework | CodeIgniter 4 | ^4.0 (installed v4.7.4) |
| UI Template | AdminLTE 4 | ^4.0 (Bootstrap 5) |
| Frontend Package | npm / Node.js | - |
| Database | MySQL / SQLite3 (testing) | - |
| Testing | PHPUnit | 10.5 |
| HTTP Client | CI4 CURLRequest | framework built-in |

## Directory Structure

```
BASE/
|-- app/
|   |-- Config/          # Configuration (Routes, Filters, NeoFeeder, Services, etc.)
|   |-- Controllers/     # Login, Dashboard, Home, BaseController
|   |-- Database/        # Migrations & Seeds
|   |-- Filters/         # AuthFilter (route protection middleware)
|   |-- Helpers/         # Helper functions
|   |-- Libraries/       # Auth.php, NeoFeeder.php (service layer)
|   |-- Models/          # Database models
|   |-- Views/           # login/, dashboard/, layout/, errors/
|-- docs/                # Project documentation (with docs/archived/ for archives)
|-- public/              # Document root (index.php)
|   |-- adminlte/        # AdminLTE 4 CSS/JS (from npm build)
|   |-- bootstrap/       # Bootstrap 5 CSS/JS (from npm build)
|   |-- fontawesome/     # Font Awesome 7 CSS/webfonts (from npm build)
|-- tests/               # Unit & integration tests
|-- package.json         # Frontend dependencies (npm)
|-- node_modules/        # Frontend packages (generated)
|-- vendor/              # Backend dependencies (Composer)
|-- writable/            # Logs, cache, uploads
```

## Neo Feeder API

The API base URL is configured via `neofeeder.apiBaseUrl` in `.env` (see the `env` template).

### Authentication (GetToken)

**Request:**
```json
{
    "act": "GetToken",
    "username": "...",
    "password": "..."
}
```

**Response (success):**
```json
{
    "error_code": 0,
    "error_desc": "",
    "data": {
        "token": "..."
    }
}
```

## Setup

1. Clone this repository into your web server directory.
2. Copy `env` to `.env` and adjust the `baseURL` and database connection configuration.
3. Run `composer install` to install backend dependencies.
4. Run `npm install && npm run build` to install and copy frontend assets to `public/`.
5. Point your web server to the `public/` folder.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the contribution workflow, coding conventions, and pull request checklist.

## License

This project is licensed under the [MIT License](LICENSE).
