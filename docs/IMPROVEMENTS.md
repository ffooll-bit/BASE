# IMPROVEMENTS

_The tracker for feature ideas, found bugs, and optimization plans. Each finding is recorded as an item under Items: copy the template below, fill it in, and place the item at the very bottom of the Items section._

## Item Template

```markdown
### <ID> — <Title>
- **Status:** `recorded` | `verified` | `rejected` | `implemented`
- **Issue:** <#NN> | `—`
- **Recorded:** YYYY-MM-DD HH:MM
- **Implemented:** YYYY-MM-DD HH:MM | `—`
- **Problem:** ...
- **Possible Fix:** ...
- **Actual Fix:** ...
- **Rejection Reason:** ...
- **Actual Implemented:** ...
- **Changes:** ...
```

Item IDs follow the format `<LABEL_CODE>-<NNN>` built from the default GitHub labels, with numbers counted per label code:

| GitHub Label | Code |
|--------------|------|
| `bug` | BUG |
| `documentation` | DOC |
| `enhancement` | ENH |
| `duplicate` | DUP |
| `good first issue` | GFI |
| `help wanted` | HW |
| `invalid` | INV |
| `question` | QST |
| `wontfix` | WFX |

## Items

### ENH-001 — Decide the platform path for the blocked phpunit 10 → 13 upgrade
- **Status:** `verified`
- **Issue:** `#136`
- **Recorded:** 2026-11-09 14:48
- **Implemented:** `—`
- **Problem:** The Dependabot pull request upgrading `phpunit/phpunit` from 10.5.27 to 13.4.6 cannot be merged. It is not a test failure: `phpunit 13.4.6`, the `sebastian/*` packages, and `php-code-coverage 14.5.1` all require PHP >= 8.4 / 8.4.1, while the project pins `"php": "^8.2"` in `composer.json` and CI runs PHP 8.2, so `composer install` aborts with `Problem 1`. Until the platform decision is made the pull request stays open, and the next developer-tool upgrade risks hitting the same wall.
- **Possible Fix:** Take an explicit decision and record it on this item: (a) raise the platform to PHP 8.4 — update `composer.json`, the CI workflow, and verify locally (`php -l`, `php-cs-fixer`, full `phpunit` run) before merging the pull request; or (b) defer — close it with `@dependabot ignore this major version` and keep `phpunit ^10.5` until PHP 8.4 is viable.
- **Actual Fix:** Decision item with three data-backed paths: **(A, recommended)** raise the platform to PHP 8.4.1+ — install PHP 8.4 for XAMPP, set `php-version: '8.4'` in `ci.yml`, change `composer.json` to `"php": "^8.4"` and `phpunit/phpunit: "^13.4"`, update the `phpunit.xml.dist` schema to 13.x, run the full suite, then merge the Dependabot pull request; **(B)** intermediate path: PHP 8.3 + PHPUnit 12 (bugfix support until February 2027); **(C)** defer: close the pull request with `@dependabot ignore this major version` and add an `ignore` rule to `.github/dependabot.yml`, accepting that PHPUnit 10 is already end-of-life. Verified facts: PHPUnit 13 requires PHP 8.4 (phpunit.de supported versions; 13.4.x requires 8.4.1 per Composer), PHPUnit 11 supports 8.2 and PHPUnit 12 supports 8.3; CodeIgniter 4.7.6, PhpSpreadsheet 5.10, php-cs-fixer, Faker, and vfsstream all accept PHP 8.4. Gate before merge: `php -l`, `php-cs-fixer`, and the full `phpunit` suite green on the new PHP.
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-002 — Export the full student roster to Excel (port GetMahasiswaFeeder)
- **Status:** `verified`
- **Issue:** `#137`
- **Recorded:** 2026-11-09 14:48
- **Implemented:** `—`
- **Problem:** The `Mahasiswa` page in BASE only browses paginated student data; there is no way to download the entire roster as one Excel workbook with the derived columns and data-quality notes produced by the local `GetMahasiswaFeeder` bot. That capability currently exists only in a separate Flask application that stores its own Neo Feeder credentials in `.env` and runs on `127.0.0.1:5001`, so producing the report means leaving BASE and maintaining a second deployment.
- **Possible Fix:** Port the capability into BASE as a native CI4 action (PhpSpreadsheet is already a dependency): fetch `GetListMahasiswa` plus `GetListRiwayatPendidikanMahasiswa` and `GetSemester`, then generate an `.xlsx` with the same columns — NIM, NAMA, JURUSAN as `Prodi (Jenjang)`, ANGKATAN, JENIS PENDAFTARAN, PERIODE MASUK, TANGGAL MASUK, STATUS SAAT INI, PERIODE KELUAR, TANGGAL KELUAR/YUDISIUM, CATATAN — applying the same note rules (duplicate NIM with differing or identical details, `periode keluar kosong`, `tanggal keluar kosong`) plus the `Tanpa NIM` sheet. Reuse the BASE session token instead of a separate credential settings page.
- **Actual Fix:** Add a native CI4 export action (for example `Mahasiswa::export` plus a route and a button on the Mahasiswa page) that fetches `GetListMahasiswa` and `GetListRiwayatPendidikanMahasiswa` joined by `id_registrasi_mahasiswa` (a `nim` filter returns incomplete riwayat for duplicated NIMs) plus `GetSemester` for the periode-keluar labels, then writes an `.xlsx` with PhpSpreadsheet using the same 11 columns (NIM, NAMA, JURUSAN as `Prodi (Jenjang)`, ANGKATAN, JENIS PENDAFTARAN, PERIODE MASUK, TANGGAL MASUK, STATUS SAAT INI, PERIODE KELUAR, TANGGAL KELUAR/YUDISIUM, CATATAN) and the same note rules (`NIM kosong di sumber` with a `Tanpa NIM` sheet, `riwayat pendidikan tidak ditemukan`, `periode keluar kosong di sumber`, `tanggal keluar kosong di sumber`, duplicate NIM identical versus differing). Source dates pass through as returned by the API — they are `dd-mm-yyyy` strings, not `Y-m-d`. Verified scale: 26,176 source rows become 26,152 export rows, 23 rows without NIM, 8 duplicated NIMs, 0 missing riwayat, a 1.42 MB workbook, and roughly one minute of fetching. Use the BASE session token, no credential page. Because the fetch is large, run with `set_time_limit(0)` and explicit memory headroom; if memory proves insufficient, split into two steps (snapshot the fetched rows in cache, then build and download the file).
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-003 — Bulk transcript-grade download per semester (port GetNilaiFeeder)
- **Status:** `verified`
- **Issue:** `#138`
- **Recorded:** 2026-11-09 14:48
- **Implemented:** `—`
- **Problem:** BASE has no page for downloading course grades in bulk; transcript inspection in the PISN wizard is per student, inside a wizard step. The local `GetNilaiFeeder` bot provides a flow with no BASE equivalent: upload a NIM list, review NIM and name, pick semesters, fetch `GetRiwayatNilaiMahasiswa` and `GetAktivitasKuliahMahasiswa`, and receive an Excel workbook with one sheet per semester where each student is a block (header `NIM | Nama | Program Studi (Jenjang)`, then course rows, then an `IPS | IPK | Status` summary row, then a blank row) with live progress.
- **Possible Fix:** Add a native BASE page: upload an Excel file with a `nim` column, review the resolved names via `GetListMahasiswa`, pick semesters from `GetSemester`, fetch data through a new `NeoFeeder::getRiwayatNilaiMahasiswa` method (act `GetRiwayatNilaiMahasiswa`, not used by BASE yet) together with `GetAktivitasKuliahMahasiswa`, then generate an `.xlsx` with the same sheet and block structure. Because many NIMs across semesters can exceed the PHP request time limit, process them in batches with stored progress (the `WizardProgress` pattern) or bound the work per request.
- **Actual Fix:** Add `NeoFeeder::getRiwayatNilaiMahasiswa()` through `sendListRequest` (act `GetRiwayatNilaiMahasiswa`, confirmed in the official Web Service guide) and a new menu: upload an Excel file with a `nim` column (fall back to the first column, deduplicate), review the resolved names via `GetListMahasiswa` in chunks of 50 with a `nim IN (...)` filter, pick one or more semesters from `GetSemester`, then fetch `GetRiwayatNilaiMahasiswa` and `GetAktivitasKuliahMahasiswa` in chunks of 25 with `nim IN (...)` filters and `id_periode asc` / `id_semester asc` ordering, keeping a per-NIM fallback that records failures. Generate an `.xlsx` with one sheet per selected semester (sorted by `id_semester`, title from `nama_semester` sanitized to 31 unique characters); per student a bold header `NIM | Nama | Prodi (Jenjang)`, bold highlighted column headers `Mata Kuliah | SKS | Kelas | Nilai Huruf | Nilai Indeks | Nilai Angka | Status Sync`, course rows sorted by course name, `(tidak ada data nilai pada semester ini)` when empty, an `IPS: … | IPK: … | Status: …` summary row, and a blank separator row; add a `Gagal` sheet (`NIM | Alasan`) when any NIM failed. Bound the work per request and keep progress in cache following the `VerifikasiIpkStore` pattern so a long run does not hit the PHP time limit.
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`
