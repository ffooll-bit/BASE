# BASE (Bongaya Advanced Services Engine)

## Tentang Proyek

BASE adalah aplikasi berbasis web yang dibangun di atas framework CodeIgniter 4. Tujuan utama aplikasi ini adalah sebagai **Web Service Client** untuk **Neo Feeder** -- yaitu aplikasi yang berfungsi untuk menyinkronkan data dengan database di PDDIKTI.

Saat ini, BASE hanya berperan sebagai **Client** dari Web Service Neo Feeder. Namun ke depannya, aplikasi ini akan dikembangkan agar dapat terintegrasi dengan aplikasi-aplikasi lain di lingkungan STIEM Bongaya.

Nama **BASE** (Bongaya Advanced Services Engine) dipilih karena aplikasi ini akan menjadi **dasar** atau fondasi integrasi bagi sistem-sistem lainnya.

## Teknologi

- **Framework:** CodeIgniter 4 (PHP ^8.1)
- **Template:** AdminLTE 3.2
- **Database:** MySQL (default), SQLite3 (testing)
- **Testing:** PHPUnit 10.5

## Server Requirements

- PHP version 8.1 or higher
- Extensions: intl, mbstring, json, mysqlnd, libcurl

## Setup

1. Clone repositori ini ke direktori web server Anda.
2. Copy `env` menjadi `.env` dan sesuaikan konfigurasi `baseURL` serta koneksi database.
3. Jalankan `composer install` untuk menginstall dependencies.
4. Arahkan web server ke folder `public/`.

## Struktur Direktori

```
BASE/
├── app/          # Kode aplikasi (Controllers, Models, Views, dll)
├── public/       # Document root (index.php)
├── tests/        # Unit dan integration tests
├── vendor/       # Dependencies (Composer)
├── writable/     # Direktori writable (logs, cache, uploads)
└── .env          # Konfigurasi lingkungan (copy dari env)
```
