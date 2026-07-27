# BASE (Bongaya Advanced Services Engine)

## Tentang Proyek

BASE adalah aplikasi berbasis web yang dibangun di atas framework CodeIgniter 4. Tujuan utama aplikasi ini adalah sebagai **Web Service Client** untuk **Neo Feeder** -- yaitu aplikasi yang berfungsi untuk menyinkronkan data dengan database di PDDIKTI.

Saat ini, BASE berperan sebagai **Client** dari Web Service Neo Feeder. Namun ke depannya, aplikasi ini akan dikembangkan agar dapat terintegrasi dengan aplikasi-aplikasi lain di lingkungan STIEM Bongaya.

Nama **BASE** (Bongaya Advanced Services Engine) dipilih karena aplikasi ini akan menjadi **dasar** atau fondasi integrasi bagi sistem-sistem lainnya.

## Status Pengembangan

Dua fitur utama telah selesai:

1. **Login / Autentikasi** - Terimplementasi dan terverifikasi (10/10 E2E pass). Sistem login menggunakan API Neo Feeder (`GetToken`) untuk autentikasi, dengan session-based auth via CodeIgniter 4.
2. **AdminLTE 3 ke AdminLTE 4 Migration** - UI layer dimigrasi dari AdminLTE 3.2 (Bootstrap 4, jQuery) ke AdminLTE 4 (Bootstrap 5, vanilla JS, Font Awesome 6).

## Teknologi

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | CodeIgniter 4 | ^4.0 (terinstall v4.5.3) |
| Template UI | AdminLTE 4 | ^4.0 (Bootstrap 5) |
| Frontend Package | npm / Node.js | - |
| Database | MySQL / SQLite3 (testing) | - |
| Testing | PHPUnit | 10.5 |
| HTTP Client | CI4 CURLRequest | bawaan framework |

## Struktur Direktori

```
BASE/
|-- app/
|   |-- Config/          # Konfigurasi (Routes, Filters, NeoFeeder, Services, dll)
|   |-- Controllers/     # Login, Dashboard, Home, BaseController
|   |-- Database/        # Migrations & Seeds
|   |-- Filters/         # AuthFilter (middleware proteksi route)
|   |-- Helpers/         # Helper functions
|   |-- Libraries/       # Auth.php, NeoFeeder.php (service layer)
|   |-- Models/          # Model database
|   |-- Views/           # login/, dashboard/, layout/, errors/
|-- docs/                # Dokumentasi proyek
|-- public/              # Document root (index.php)
|   |-- adminlte/        # AdminLTE 4 CSS/JS (dari npm build)
|   |-- bootstrap/       # Bootstrap 5 CSS/JS (dari npm build)
|   |-- fontawesome/     # Font Awesome 6 CSS/webfonts (dari npm build)
|-- tests/               # Unit & integration tests
|-- package.json         # Frontend dependencies (npm)
|-- node_modules/        # Frontend packages (generated)
|-- vendor/              # Backend dependencies (Composer)
|-- writable/            # Logs, cache, uploads
```

## API Neo Feeder

Endpoint: `https://neofeeder.stiem-bongaya.ac.id/ws/live2.php`

### Autentikasi (GetToken)

**Request:**
```json
{
    "act": "GetToken",
    "username": "...",
    "password": "..."
}
```

**Response (sukses):**
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

1. Clone repositori ini ke direktori web server Anda.
2. Copy `env` menjadi `.env` dan sesuaikan konfigurasi `baseURL` serta koneksi database.
3. Jalankan `composer install` untuk menginstall dependencies backend.
4. Jalankan `npm install && npm run build` untuk menginstall dan menyalin assets frontend ke `public/`.
5. Arahkan web server ke folder `public/`.

## Server Requirements

- PHP version 8.1 or higher
- Extensions: intl, mbstring, json, mysqlnd, libcurl
