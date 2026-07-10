# BASE (Bongaya Advanced Services Engine)

## Tentang Proyek

BASE adalah aplikasi berbasis web yang dibangun di atas framework CodeIgniter 4. Tujuan utama aplikasi ini adalah sebagai **Web Service Client** untuk **Neo Feeder** -- yaitu aplikasi yang berfungsi untuk menyinkronkan data dengan database di PDDIKTI.

Saat ini, BASE berperan sebagai **Client** dari Web Service Neo Feeder. Namun ke depannya, aplikasi ini akan dikembangkan agar dapat terintegrasi dengan aplikasi-aplikasi lain di lingkungan STIEM Bongaya.

Nama **BASE** (Bongaya Advanced Services Engine) dipilih karena aplikasi ini akan menjadi **dasar** atau fondasi integrasi bagi sistem-sistem lainnya.

## Status Pengembangan

Fitur pertama (**Login / Autentikasi**) telah selesai diimplementasi dan terverifikasi (10/10 E2E pass). Sistem login menggunakan API Neo Feeder (`GetToken`) untuk autentikasi, dengan session-based auth via CodeIgniter 4.

Proyek saat ini dalam tahap perencanaan untuk fitur selanjutnya.

## Teknologi

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| Framework | CodeIgniter 4 | ^4.0 (terinstall v4.5.3) |
| Template UI | AdminLTE | 3.2 (Bootstrap 4) |
| Database | MySQL / SQLite3 (testing) | - |
| Testing | PHPUnit | 10.5 |
| HTTP Client | CI4 CURLRequest | bawaan framework |

## Struktur Direktori

```
BASE/
├── app/
│   ├── Config/          # Konfigurasi (Routes, Filters, NeoFeeder, Services, dll)
│   ├── Controllers/     # Login, Dashboard, Home, BaseController
│   ├── Database/        # Migrations & Seeds
│   ├── Filters/         # AuthFilter (middleware proteksi route)
│   ├── Helpers/         # Helper functions
│   ├── Libraries/       # Auth.php, NeoFeeder.php (service layer)
│   ├── Models/          # Model database
│   └── Views/           # login/, dashboard/, layout/, errors/
├── docs/
│   ├── specifications/  # Dokumen spesifikasi fitur
│   ├── plans/           # Dokumen technical plan
│   └── tasks/           # Task backlog
├── public/              # Document root (index.php)
├── tests/               # Unit & integration tests
├── vendor/              # Dependencies (Composer)
└── writable/            # Logs, cache, uploads
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
3. Jalankan `composer install` untuk menginstall dependencies.
4. Arahkan web server ke folder `public/`.

## Server Requirements

- PHP version 8.1 or higher
- Extensions: intl, mbstring, json, mysqlnd, libcurl
