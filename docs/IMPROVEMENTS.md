# Repository Improvement Plan

> This file tracks every planned improvement, feature, bug fix, and implementation step requested in plan mode. Items move through a lifecycle and are archived on the maintainer's instruction.

## Lifecycle

Every item in this file follows this flow:

```
Recorded → Verified → Issue → Implemented → Archived
```

1. **Recorded** — logged here when a review/finding/feature/fix is discussed in plan mode. Item gets `Problem` and `Possible Fix`.
2. **Verified** — a deeper one-by-one review confirms the item is valid (or rejects it).
   - Valid → a GitHub Issue is created, its number is recorded in `Issue`, and `Actual Fix` is filled in.
   - Invalid → the item is **skipped** (marked `rejected`, not tracked further).
3. **Implemented** — the linked GitHub Issue is closed. The item is updated with `Actual Implemented` and `Changes`, and `Issue` keeps the **Issue number** (not the PR number).
4. **Archived** — moved to `docs/archived/` with a date suffix (`IMPROVEMENT_YYYY-MM-DD.md`) **only when the maintainer explicitly instructs it**. Never archived automatically when items are done.

New findings only enter this file while it is **not archived**. Once archived, a fresh `IMPROVEMENTS.md` is created for the next cycle.

## ID Scheme

Each item ID is `<LABEL_CODE>-<NNN>`, built from the **default GitHub labels** (no custom labels). The number counts up per label code, so each sequence is independent and never runs out.

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

The label is encoded directly in the ID prefix. When a valid item becomes a GitHub Issue, the corresponding label is applied to it.

## Status Legend

| Status | Meaning |
|--------|---------|
| `recorded` | Logged, not yet deeply reviewed |
| `verified` | Deep review confirmed valid, Issue created |
| `rejected` | Deep review found it invalid — skipped |
| `implemented` | Linked Issue is closed |

## Field Guide

| Field | When filled | Content |
|-------|-------------|---------|
| `Problem` | `recorded` | The finding described as application behavior when possible; for non-application items (CI, settings, tooling), describe the impact instead. |
| `Possible Fix` | `recorded` | The initial fix plan — written while still `recorded`, so it is not guaranteed to work. |
| `Actual Fix` | `verified` | The final fix plan confirmed during deep review. |
| `Actual Implemented` | `implemented` | What was actually changed during implementation. |
| `Changes` | `implemented` | The behavior changes that result after `implemented`. |
| `Issue` | `verified` | The GitHub Issue number (`#N`); recorded once the issue is opened. |

## Item Template

```markdown
### <ID> — <Title>
- **Status:** `verified` | `verified` | `rejected` | `implemented`
- **Issue:** <#NN> | `—`
- **Recorded:** YYYY-MM-DD
- **Implemented:** YYYY-MM-DD | `—`
- **Problem:** ...
- **Possible Fix:** ...
- **Actual Fix:** ...
- **Actual Implemented:** ...
- **Changes:** ...
```

---

## Active Items

### ENH-001 — Hide UUID/random-string ID columns in PISN Graduation verification step
- **Status:** `implemented`
- **Issue:** #85
- **Recorded:** 2026-08-30 02:21
- **Implemented:** 2026-08-30 03:44
- **Problem:** In `app/Views/graduation/wizard.php` the identity table (lines 41–43) and the academic table (lines 64–69) render every key returned by the Neo Feeder API, including columns whose values are random UUID strings (e.g. `id_registrasi_mahasiswa`, `id_mahasiswa`, `id_aktivitas_kuliah`). These columns are meaningless to the admin and clutter the verification UI. The same pattern exists in the Mahasiswa, Aktivitas Kuliah, and Mahasiswa Lulus-DO menus, but the current scope is PISN Graduation only.
- **Possible Fix:** In `wizard.php`, suppress columns whose values are UUID/random strings from both the identity and academic tables. Keep functional, non-random columns such as `id_semester`. Implement an explicit allow/deny list of column keys to hide. Other menus stay out of scope for now.
- **Actual Fix:** In `app/Views/graduation/wizard.php`, define a deny-list of column keys whose values are UUID/random strings and skip them when rendering both the identity table (lines 41–43) and the academic table (lines 64–69). Deny-list: `id_registrasi_mahasiswa`, `id_mahasiswa`, `id_aktivitas_kuliah` (plus any other UUID-valued `*_id` keys observed in the live response). Keep `id_semester` and all human-readable columns visible. Scope: PISN Graduation wizard only; other menus stay out of scope.
- **Actual Implemented:** Every UUID-valued column is hidden from both wizard tables. `Graduation::step()` passes `$uuidKeys` (explicit deny-list `id_registrasi_mahasiswa`, `id_mahasiswa`, `id_aktivitas_kuliah`) and `$uuidRegex` (`/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i`) to `graduation/wizard`. The identity table skips a row whose key is in `$uuidKeys` or whose value matches `$uuidRegex`; the academic table builds visible columns by skipping any column whose sample value matches `$uuidRegex` (header + cells stay aligned). `id_semester` (code e.g. `20252`, not a UUID) and all human-readable columns remain. Verified live via Playwright: 0 UUID cells in the Identitas and Akademik tables.
- **Changes:** `app/Controllers/Graduation.php` (pass `uuidKeys` + `uuidRegex` to `graduation/wizard`), `app/Views/graduation/wizard.php` (value-based UUID skip in identity + academic tables).

### ENH-002 — Sort academic table by id_semester ascending
- **Status:** `implemented`
- **Issue:** #86
- **Recorded:** 2026-08-30 02:21
- **Implemented:** 2026-08-30 04:08
- **Problem:** `Graduation::step()` calls `getAktivitasKuliahMahasiswa` without an order parameter (`app/Controllers/Graduation.php:193–199`), so the academic table (card 2) displays rows in the raw API response order rather than chronological semester order, making the academic-history review hard to read.
- **Possible Fix:** Sort the academic rows by `id_semester` ascending (20261 → 20262 …). Decide between a PHP `usort` on the returned array versus passing an `order` parameter to the API; confirm during verification which approach is reliable.
- **Actual Fix:** In `Graduation::step()`, pass `order => 'id_semester asc'` to `getAktivitasKuliahMahasiswa` (`sendListRequest` already forwards `order`; `app/Libraries/NeoFeeder.php:142–152`). As a defensive fallback, apply a PHP `usort` by `id_semester` (string ascending) on the returned array in case the API does not honor the `order` parameter. Keep the `id_semester` column visible (used for sorting and display).
- **Actual Implemented:** `Graduation::step()` now passes `order => 'id_semester asc'` to `getAktivitasKuliahMahasiswa` and additionally applies a defensive PHP `usort` by `id_semester` (string ascending) on the returned `$academic` array, so the academic table (card 2) is always sorted oldest→newest semester regardless of API ordering. `id_semester` column remains visible.
- **Changes:** `app/Controllers/Graduation.php` (add `order` + `usort` in `step()`).

### ENH-003 — Remove two unused inputs; make academic table inline-editable, stored in wizard
- **Status:** `implemented`
- **Issue:** #87
- **Recorded:** 2026-08-30 02:21
- **Implemented:** 2026-08-30 04:30
- **Problem:** `wizard.php` lines 73–81 render a "Catatan akademik (opsional)" textarea (`academic_flag`) and a "Biaya Kuliah Semester" input (`biaya_kuliah`). `Graduation::stepPost` reads and validates them (`Graduation.php:286,288,312–314`) but `finish()` never sends them to Neo Feeder, so they are collected yet unused. The user instead wants the academic-history table (status, IPK, IPS per semester) in step 2 to be inline-editable.
- **Possible Fix:** Remove the `academic_flag` and `biaya_kuliah` fields (view + controller read/validation). Make the academic table rows editable inline (status, IPK, IPS columns). Store the corrected values in the `WizardProgress` state and submit them at `finish()` together with the graduation data (not pushed directly to Neo Feeder). The endpoint/record schema for sending academic edits must be confirmed against the WS guide during implementation.
- **Actual Fix:** (1) Remove the `academic_flag` textarea and `biaya_kuliah` input from `wizard.php:73–81`, and remove their read/validation in `Graduation::stepPost` (`Graduation.php:286,288,312–314`), including the `ponytail` comment at `:311`. (2) Make the academic table (card 2) rows inline-editable for the `status`, `ipk`, `ips` columns. (3) Store the edited values per student in the `WizardProgress` state. (4) At `finish()`, after building the `insertMahasiswaLulusDO` record, also push each edited academic row to Neo Feeder via `updatePerkuliahanMahasiswa` (key = `id_registrasi_mahasiswa` + `id_semester`; record = edited `status`/`ipk`/`ips`). Per user decision, the academic edits ARE pushed to Neo Feeder at `finish()`. The exact `updatePerkuliahanMahasiswa` record field names to be confirmed against `docs/NeoFeederWSGuide.md` during implementation.
- **Actual Implemented:** `academic_flag` textarea and `biaya_kuliah` input removed from `wizard.php` (card 2); their read/validation in `Graduation::stepPost` removed. The academic table (card 2) renders `ips`/`ipk` as inline `<input>` cells and `id_status_mahasiswa` as an inline `<select>` dropdown whose options come from Neo Feeder `GetStatusMahasiswa` (codes/names observed live: A=Aktif, C=Cuti, G=Sedang Double Degree, M=Kampus Merdeka, N=Non-Aktif, U=Menunggu Ujian); `nama_status_mahasiswa` stays a read-only label; all editable cells are keyed by `id_semester` and pre-filled from the saved `WizardProgress` state (falling back to the live row value). Edited values are stored per student in `WizardProgress` (`$student['academics']`, keyed by `id_semester`). At `finish()`, after `insertMahasiswaLulusDO`, each edited semester is pushed to Neo Feeder via `updatePerkuliahanMahasiswa(token, id_registrasi_mahasiswa, id_semester, {id_status_mahasiswa, ips, ipk})` — `id_registrasi_mahasiswa` is resolved by re-fetching `getAktivitasKuliahMahasiswa` for the student's nim. The `UpdatePerkuliahanMahasiswa` act and its `key`/`record` schema were confirmed live via `GetDictionary` (act exists; key `id_registrasi_mahasiswa`+`id_semester`; record includes `id_status_mahasiswa` `character(1)`, `ips`, `ipk`). Note: `id_status_mahasiswa` is a `character(1)` code (A/C/G/M/N/U), not a UUID, so it is unaffected by ENH-001's UUID-hiding.
- **Changes:** `app/Libraries/NeoFeeder.php` (new `getStatusMahasiswa()`), `app/Views/graduation/wizard.php` (remove 2 inputs; inline-editable academic table with `status` dropdown), `app/Controllers/Graduation.php` (`step()` loads status options + passes to view; `stepPost` stores `academics`; `finish()` pushes corrections via `updatePerkuliahanMahasiswa`).

### BUG-001 — Transcript completeness check must use "Cek Transkrip Mahasiswa" menu
- **Status:** `implemented`
- **Issue:** #89
- **Recorded:** 2026-08-30 02:21
- **Implemented:** 2026-08-30 14:40
- **Problem:** `Graduation::step()` and `checkTranscriptCompleteness` (`Graduation.php:201–211, 251–269`) assess completeness from `getTranskripMahasiswa` and detect the thesis by course name. This is inaccurate: a thesis/skripsi course may already have a grade but not yet be flagged "included in transcript", so the current logic can wrongly report the transcript as incomplete or miss it.
- **Possible Fix:** Replace the data source with the Neo Feeder menu "Perkuliahan > Cek Transkrip Mahasiswa", which exposes a per-course marker indicating inclusion in the transcript. Discover the correct API endpoint (Playwright inspection of the Neo Feeder page is permitted) and read its response schema (the inclusion-marker field). Re-implement `checkTranscriptCompleteness` against that endpoint.
- **Actual Fix:** Re-implement `checkTranscriptCompleteness` to read the per-course transcript "inclusion" marker instead of inferring from the thesis grade. The Neo Feeder menu "Perkuliahan > Cek Transkrip Mahasiswa" returns, per course, a boolean `choosed` field = "included in transcript". Endpoint observed live (Neo Feeder cloud, stiembongaya.feeder-cloud.com): `GET /ws/transkrip/nilai_mahasiswa?mahasiswa=<single student JSON>` (auth `Bearer <cloud JWT>`). The response object is `{ mahasiswa, nilai_mahasiswa: [[ …courses… ], count], nilai_transfer_mahasiswa, nilai_kampusmerdeka_mahasiswa }`; each course row carries `choosed` (bool), `nm_mk`, `kode_mk`, `id_smt`, `nilai_huruf`, `nilai_angka`. Completeness rule: the thesis/skripsi course (name matches `/skripsi|tugas\s*akhir|thesis|disertasi/i`) must exist in the transcript AND carry a non-empty grade (`nilai_huruf`/`nilai_angka`); `choosed` is informational only (whether the course is checked into the transcript) and does NOT block completeness. Verified on NIM 202010087: the `Skripsi` course (`kode_mk 20224MSSK874`, `id_smt 20252`) has `choosed:false` despite `nilai_huruf:A` — exactly the case the old heuristic misses.

  OPEN DESIGN DECISION resolved during implementation: WS `act` `GetTranskripMahasiswa` does NOT return `choosed` (confirmed live via GetDictionary schema + data: no `choosed` column). The cloud endpoint `/ws/transkrip/nilai_mahasiswa` accepts BASE's existing WS `act` token (from `GetToken`) directly as an `Authorization: Bearer` header — verified live (HTTP 200, per-course `choosed` present). No separate cloud JWT / login / captcha needed. New `NeoFeeder::getCekTranskripMahasiswa()` performs the two-step cloud lookup (`cari_mahasiswa` by NIM → `nilai_mahasiswa`) using the WS token as Bearer.
- **Actual Implemented:** `NeoFeeder::getCekTranskripMahasiswa(string $token, string $nim)` added (cloud REST: GET `/ws/transkrip/cari_mahasiswa?nm_pd=<nim>` then GET `/ws/transkrip/nilai_mahasiswa?mahasiswa=<student JSON>`, `Authorization: Bearer <WS act token>`, via new private `cloudGet()` helper; returns course rows with `choosed` under `data`). `Graduation::step()` now loads the transcript from `getCekTranskripMahasiswa` (by NIM) instead of `getTranskripMahasiswa` (by `id_registrasi_mahasiswa`). `checkTranscriptCompleteness()` rewritten: transcript complete when a thesis/skripsi/tugas akhir course (name regex) EXISTS with a NON-EMPTY grade; `choosed` reported but not gating. Wizard card 2b "Kelengkapan Transkrip" expanded to show the detected thesis course detail (MK, kode, semester, nilai, status badge: "Nilai ada · masuk transkrip" / "Nilai ada · belum masuk transkrip" / "Tanpa nilai") plus "MK skripsi/tugas akhir tidak ditemukan" note when absent. `getTranskripMahasiswa` kept for other consumers. Verified live over HTTP on NIM 202010087: 74 course rows, thesis row Skripsi/20224MSSK874/20252/A/choosed=true, "Transkrip lengkap", badge "Nilai ada · masuk transkrip".
- **Changes:** `NeoFeeder.php` (+`getCekTranskripMahasiswa`, +`cloudGet`), `Graduation.php` (step transcript source, `checkTranscriptCompleteness` rewrite), `wizard.php` (card 2b thesis detail table).

### ENH-004 — Format all wizard inputs to match Neo Feeder display
- **Status:** `implemented`
- **Issue:** #88
- **Recorded:** 2026-08-30 02:21
- **Implemented:** 2026-08-30 05:50
- **Problem:** All inputs across the graduation wizard are not formatted to match how Neo Feeder displays/accepts the data — notably step 4 "Input Kelulusan" (`nim`, `nama`, `jenis_keluar`, `tgl_keluar`, `periode_keluar`, `ipk`, `no_ijazah` in `wizard.php:120–138`) and the academic inputs from ENH-003. Date, numeric value, period, and `jenis_keluar` representations differ from Neo Feeder's.
- **Possible Fix:** Adjust input formatting/validation across the whole wizard so values match Neo Feeder's representation (date format, period format, `jenis_keluar` allowed values, numeric/IPK formatting, etc.). Inspect Neo Feeder input types directly (Playwright permitted) to confirm the exact formats. Applies to every input in the wizard.
- **Actual Fix:** Align the step 4 "Input Kelulusan" inputs to Neo Feeder's actual form (`#/mahasiswalulusdo/add`, observed live):
  - `jenis_keluar`: replace the free-text input with a dropdown of PDDIKTI `jenis_keluar` labels — `Lulus`, `Mutasi`, `Dikeluarkan`, `Mengajukan pengunduran diri`, `Putus Studi`, `Meninggal dunia`, `Selesai Pendidikan Non Gelar` — storing the corresponding `id_jns_keluar` code.
  - `periode_keluar`: replace the free-text `2026.1` input with a semester dropdown (`id_smt`), displayed as `2025/2026 Genap` / `2026/2027 Ganjil` etc., sourced from the Neo Feeder semester list; store `id_smt`.
  - `tgl_keluar` / `tanggal SK`: date input — Neo Feeder stores `YYYY-MM-DD` (native `type=date` matches; the Neo Feeder UI displays `DD-MM-YYYY`). Keep `type=date`.
  - `ipk`: text/number, decimal uses a dot (Neo Feeder placeholder: "*untuk decimal menggunakan titik"); matches native.
  - `no_ijazah`: free text (maps to Neo Feeder "No Ijazah / No sertifikat profesi").
  - The same date/decimal formatting applies to the ENH-003 academic edits (`status`, `ipk`, `ips`).

  The wizard currently sends these as free text to `insertMahasiswaLulusDO`; `jenis_keluar` and `periode_keluar` must send the codes (`id_jns_keluar`, `id_smt`), not the labels. Exact WS `record` field names to be confirmed against `docs/NeoFeederWSGuide.md` during implementation.
- **Actual Implemented:** Rebuilt the step 4 "Input Kelulusan" inputs live-schema-driven and fixed the `InsertMahasiswaLulusDO` record. Added `NeoFeeder::getJenisKeluar()` and `getSemester()` (sendListRequest pattern). `Graduation::step()` fetches both reference lists (id→label) and passes `jenisKeluarOptions`/`semesterOptions` to the view. `wizard.php` card 4: `jenis_keluar` is now a dropdown of 7 PDDIKTI labels storing the `id_jenis_keluar` code (label→code reverse-map so a label pre-filled in Excel resolves to its code); `periode_keluar` is now a semester dropdown storing `id_semester` (`2025/2026 Genap` etc.); `tgl_keluar` is `type="date"` (`YYYY-MM-DD`). Live `GetDictionary` confirmed the real `InsertMahasiswaLulusDO` schema differs from the prior record — record rebuilt to send `id_registrasi_mahasiswa` (resolved from `getAktivitasKuliahMahasiswa`, required PK), `id_jenis_keluar`, `tanggal_keluar`, `id_periode_keluar`, `ipk`, `nomor_ijazah`; the obsolete `nim`/`nama` fields removed (not WS fields). Verified live via Playwright: dropdowns (+selection/values), `tgl_keluar type=date`, no Neo Feeder mutation.
- **Changes:** `app/Libraries/NeoFeeder.php` (+2 methods), `app/Controllers/Graduation.php` (fetch options in `step()`, rebuild record in `finish()`), `app/Views/graduation/wizard.php` (card 4 dropdowns + date input + label→code mapping).
