# BASE (Backbone for Academic Services & Education)

[![CI](https://github.com/ffooll-bit/BASE/actions/workflows/ci.yml/badge.svg)](https://github.com/ffooll-bit/BASE/actions/workflows/ci.yml) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## About

BASE is a web application built on CodeIgniter 4. Its primary purpose is to act as a **Web Service Client** for **Neo Feeder** — the application that synchronizes data with the PDDIKTI database.

Currently, BASE acts as a **client** of the Neo Feeder web service. Going forward, the application will be developed to integrate with other academic applications.

The name **BASE** (Backbone for Academic Services & Education) reflects that the application will serve as the **foundation** for integrating other systems.

## Development Status

The application is functional and covers both authentication and the Neo Feeder data-browsing / administration flows:

1. **Login / Authentication** - The login system uses the Neo Feeder API (`GetToken`) for authentication, with session-based auth via CodeIgniter 4 and an auth filter protecting all routes except the login flow.
2. **AdminLTE 3 → AdminLTE 4 Migration** - The UI layer migrated from AdminLTE 3.2 (Bootstrap 4, jQuery) to AdminLTE 4 (Bootstrap 5, vanilla JS, Font Awesome 7).
3. **Institution & student data pages** - Pages browsing Neo Feeder data read-only through the service layer:
   - **Profil Perguruan Tinggi** (`/profil-pt`) - institution profile (identity, contact, address, legalitas).
   - **Mahasiswa** (`/mahasiswa`) - student list with edit and read-only detail.
   - **Aktivitas Kuliah** (`/aktivitas-kuliah`) - course-activity list with edit.
   - **Mahasiswa Lulus / DO** (`/mahasiswa-lulus-do`) - graduated / dropped-out student list.
4. **PISN Graduation wizard** (`/graduation`) - Excel upload of prospective graduates, sequential per-student verification (identity → academic → transcript completeness → PISN eligibility → graduation fields), pre-submit preview, and submission to Neo Feeder via `InsertMahasiswaLulusDO` (`UpdateNilaiPerkuliahanKelas` for missing thesis grades). Progress survives auth-session expiry via a resume cookie + cache-backed store.
5. **Verifikasi IPK** (`/verifikasi-ipk`) - bulk check and correction of last-semester IPK against an Excel file, applying fixes via `UpdatePerkuliahanMahasiswa`.

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
|   |-- Controllers/     # Home, Login, Dashboard, ProfilPT, Mahasiswa, AktivitasKuliah,
|   |                    # MahasiswaLulusDo, Graduation, VerifikasiIpk, BaseController
|   |-- Database/        # Migrations & Seeds
|   |-- Filters/         # AuthFilter (route protection middleware)
|   |-- Helpers/         # Helper functions
|   |-- Libraries/       # Auth, NeoFeeder, PisnService, WizardProgress, VerifikasiIpkStore (service layer)
|   |-- Models/          # Database models
|   |-- Views/           # login/, layout/, dashboard/, profil_pt/, mahasiswa/,
|   |                    # aktivitas_kuliah/, mahasiswa_lulus_do/, graduation/, verifikasi_ipk/, errors/
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

### Request / Response envelope

Every WS call answers with a standard envelope:

```json
{
    "error_code": 0,
    "error_desc": "",
    "data": null
}
```

`error_code` is `0` on success (with `data` populated for list/`Get*` calls). Negative values are produced by the app itself for connection failure (`-1`) and malformed responses (`-2`); `100`/`-2` from the API mean the session/token is invalid.

All API calls are server-side (PHP via CI4 `CURLRequest`) using the token from `session('auth.token')` — the token never reaches the browser. Payload keys are limited to `act`, `token`, `filter`, `order`, `limit`, `offset` (plus `record`/`key` for mutations).

### List / Get endpoints

These use the `{ act, token, filter?, order?, limit?, offset? }` shape. `filter` is a SQL-style WHERE string (e.g. `"nim='202010087'"`), not a JSON object. `order` is a column name, optionally `DESC`.

| WS act | Purpose |
|---------|---------|
| `GetProfilPT` | Institution (PT) profile |
| `GetListMahasiswa` | Student list (biodata) |
| `GetAktivitasKuliahMahasiswa` | Per-semester course-activity summary (IPK, IPS, sks, status) |
| `GetListRiwayatPendidikanMahasiswa` | Study-history list; carries `id_pembiayaan` needed for `UpdatePerkuliahanMahasiswa` |
| `GetStatusMahasiswa` | Reference list of student statuses |
| `GetJenisKeluar` | Reference list of graduation exit reasons |
| `GetSemester` | Reference list of semesters |
| `GetListMahasiswaLulusDO` | Graduated / dropped-out student list |
| `GetCountMahasiswa` | Total student row count (for pagination) |
| `GetCountAktivitasKuliahMahasiswa` | Total activity row count (rejects `nim`/`id_mahasiswa` filters) |
| `GetCountMahasiswaLulusDO` | Total graduated/dropped-out row count |
| `GetTranskripMahasiswa` | Student transcript grade rows |
| `GetBiodataMahasiswa` | Single student biodata (`filter`: `id_mahasiswa='...'`) |
| `GetDetailPerkuliahanMahasiswa` | Single course-activity record (`filter`: `id_registrasi_mahasiswa='...' AND id_semester='...'`) |

### Mutation endpoints

These use the `{ act, token, key?, record? }` shape — `key` holds the primary key for Update/Delete, `record` holds the fields for Insert/Update.

| WS act | `key` | Notes |
|--------|-------|-------|
| `InsertMahasiswaLulusDO` | — | Graduated/dropped-out record |
| `InsertBiodataMahasiswa` / `UpdateBiodataMahasiswa` / `DeleteBiodataMahasiswa` | `id_mahasiswa` (Update/Delete) | Student biodata |
| `InsertPerkuliahanMahasiswa` / `UpdatePerkuliahanMahasiswa` / `DeletePerkuliahanMahasiswa` | `id_registrasi_mahasiswa` + `id_semester` | Course-activity record |
| `UpdateNilaiPerkuliahanKelas` | `id_registrasi_mahasiswa` + `id_kelas_kuliah` | Class grade (e.g. `nilai_huruf`) |

Mutations require a complete record: Neo Feeder rejects partial updates, so every NOT-NULL field (plus fields such as `biaya_kuliah_smt` and `id_pembiayaan` from `GetListRiwayatPendidikanMahasiswa`) must be present.

### Cloud REST endpoints

The transcript-check feature (`getCekTranskripMahasiswa`) calls two cloud REST endpoints under the same API base host instead of a WS `act`. They authenticate with `Authorization: Bearer <token>` (the same token issued by `GetToken`).

| Endpoint | Query | Purpose |
|----------|-------|---------|
| `/ws/transkrip/cari_mahasiswa` | `nm_pd=<nim>` | Find a student |
| `/ws/transkrip/nilai_mahasiswa` | `mahasiswa=<json>` | Transcript grade rows for one student |

Query one student at a time; multi-student queries can overflow the URL length (HTTP 414).

### GetDictionary (schema inspection)

`GetDictionary` inspects the actual `key`/`record` schema of any WS function at runtime — useful before implementing a new endpoint, so the schema is not guessed. In this project it is called ad-hoc as a schema-inspection tool (development-time), not as part of the application's runtime request path.

**Request:**
```json
{
    "act": "GetDictionary",
    "token": "...",
    "fungsi": "<NamaFungsi>"
}
```

**Response (simplified)** — the schema of the requested function:
```json
{
    "error_code": 0,
    "error_desc": "",
    "data": {
        "nama": "UpdatePerkuliahanMahasiswa",
        "key": ["id_registrasi_mahasiswa", "id_semester"],
        "record": [
            { "name": "id_status_mahasiswa", "mandatory": true },
            { "name": "ips", "mandatory": false },
            { "name": "ipk", "mandatory": false }
        ]
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
