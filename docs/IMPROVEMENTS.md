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

### ENH-005 — Minimal Excel template (NIM, tanggal keluar, IPK only)
- **Status:** `implemented`
- **Issue:** #99
- **Recorded:** 2026-08-30 15:18
- **Implemented:** 2026-08-30 17:05
- **Problem:** The PISN Graduation Excel upload template requires more columns than the workflow actually needs. The wizard forces the admin to fill `jenis_keluar` and `periode_keluar` in the spreadsheet even though they are derivable or constant, and `nama` is optional but currently pointless — its real purpose is validating the registered name against the KTP name. This makes the template heavier than necessary and invites errors.
- **Possible Fix:** Reduce the template to a minimum: only **NIM** (used to look up the student), **tanggal keluar** (used as the step 4 Input Kelulusan value and to derive `periode_keluar`), and **IPK** (step 4 Input Kelulusan value). **Nama KTP** stays optional; when filled it becomes the source for validating that the registered student name matches the KTP name (feeds the step 1 auto-check from ENH-008). Drop the `jenis_keluar` column entirely (PISN Graduation is always `Lulus`) and drop `periode_keluar` (derived from `tanggal_keluar` by matching its range against the semester reference list — Daftar Semester, Ganjil/Genap only, Pendek excluded).
- **Actual Fix:** Trim `Graduation::EXCEL_HEADERS` to `nim`, `nama`, `tgl_keluar`, `ipk` (Graduation.php:23–30) and `downloadTemplate()` headers + example row to the same four columns (lines 558–564). `parseExcel()` keeps reading them; `jenis_keluar`/`periode_keluar` default to empty in the student record. Card 4 (wizard.php): `jenis_keluar` defaults to the `Lulus` code/option (`id_jenis_keluar` for Lulus) when empty; `periode_keluar` is derived automatically from `tgl_keluar` by range-matching the date against the `GetSemester` reference list (`tanggal_mulai`–`tanggal_selesai`), excluding `semester=3` (Pendek) entries; if no range matches, the field stays empty for manual selection. The KTP-name presence check (ENH-008) compares the optional Excel `nama` against the PDDIKTI identity field `nama_mahasiswa` (verified live; identity row key from `getListMahasiswa`).
- **Actual Implemented:** Trimmed `Graduation::EXCEL_HEADERS` to `nim`, `nama`, `tgl_keluar`, `ipk` (removed `jenis_keluar` and `periode_keluar`); `downloadTemplate()` headers and example row reduced to the same four columns. `parseExcel()` needs no change (uses `isset($colIndex[...])`, so dropped columns default to `''`). Added a new `Graduation::derivePeriodeKeluar()` helper that range-matches `tgl_keluar` against the `GetSemester` reference list (`tanggal_mulai`–`tanggal_selesai`), excluding Pendek semesters (marker `semester` == 3), returning the matching `id_semester` or `null`. Added a new `Graduation::normalizeTanggalKeluar()` helper so the `tgl_keluar` cell parses correctly regardless of how PhpSpreadsheet surfaced it — a `DateTimeInterface` object, an Excel serial date number (with `setReadDataOnly(true)` date cells arrive as serials such as `46207` → `2026-07-04`; confirmed against the user's real template file), or a pre-formatted `YYYY-MM-DD` string. In `step()`, when a student record has no `jenis_keluar` it defaults to the `id_jenis_keluar` for `Lulus` (via label→code lookup), and when `periode_keluar` is empty it defaults to the derived periode — both are pre-filled defaults the admin may override in the step 5 dropdowns. Verified live via Playwright against the real template: `tgl_keluar` shows `2026-07-04` (not the raw serial), `jenis_keluar` auto-selects `Lulus`, `periode_keluar` auto-derived (`2026-07-04` → `2025/2026 Genap`); upload page instruction text updated. Decided default-not-locked (admin may override).
- **Changes:** Excel template is now minimal (nim/nama/tgl_keluar/ipk); jenis_keluar auto-`Lulus`; periode_keluar auto-derived from tgl_keluar (Ganjil/Genap only, Pendek excluded) with manual override; `tgl_keluar` date cells parsed robustly (DateTime / Excel serial / string).

### ENH-006 — Cross-check IPK against last semester row; auto-update via button, executed at finish
- **Status:** `implemented`
- **Issue:** #100
- **Recorded:** 2026-08-30 15:18
- **Implemented:** 2026-08-30 19:58
- **Problem:** The IPK submitted in the Excel file is not validated against the academic data. The admin has to spot-check manually whether the Excel IPK matches the status/IPK of the student's last academic row; mismatch anywhere goes unnoticed and would be pushed to Neo Feeder as-is.
- **Possible Fix:** In step 2, compare the Excel IPK against the row with the latest `id_semester` in the academic table. If they differ, show a warning and a button that stages an auto-update: set the last row's IPK to the Excel value and set its `id_status_mahasiswa` to Aktif. The update is NOT applied immediately — it is stored (via the existing `WizardProgress` academics store) and executed at `finish()` through `updatePerkuliahanMahasiswa`, consistent with ENH-003's deferred-push design.
- **Actual Fix:** In `Graduation::step()`, after sorting `$academic` ascending by `id_semester` (already present), take the last row (largest `id_semester`) and compare its `ipk` with the Excel IPK (`$student['ipk']`). If they differ, pass a warning flag + the affected values to the view. Card 2 renders a warning alert and an "Auto-update" button; clicking it stages an override in `WizardProgress` (`$student['academics'][<last_id_semester>]` set to `id_status_mahasiswa`='A' — Aktif per live `GetStatusMahasiswa` — plus `ipk` = Excel value). No immediate mutation: `finish()` already pushes per-semester academic overrides via `updatePerkuliahanMahasiswa` (Graduation.php:468–501), so the staged edit rides the existing deferred-push path. Reached via a checkbox name (`auto_update_last_ipk`) or a separate small POST — exact UI detail decided at implementation time.
- **Actual Implemented:** In `Graduation::step()`, after sorting `$academic` ascending by `id_semester`, the last row (`$lastAcademic`) is isolated and `$lastIsActive` is computed via `in_array(id_semester, activeSemesterIds)`; both plus the raw Excel IPK (`$student['ipk']`) are passed to the view. In `wizard.php` card 2: an alert banner is rendered when the last row is active AND its `ipk` differs from the Excel IPK, offering an "Auto-update IPK & status ke Aktif" button; when the last row is NOT active, a plain informational warning ("Semester terakhir sudah tidak aktif, IPK terakhir tidak dapat disinkronkan otomatis") is shown without the button. The last academic row's IPK input is tagged `id="last-ipk"` and its status `<select>` `id="last-status"`; the step-5 graduation IPK input is tagged `id="graduation_ipk"`. A vanilla-JS block (inline, after the `</form>`) wires two-way realtime sync: `input` on either IPK field copies its value to the other (guard against feedback). The Auto-update button (only when last row active) reads the Excel IPK from the injected PHP value, sets the last-row IPK to it, sets the last-row status `<select>` to the code whose label is "Aktif" (resolved from `$statusOptions` — not hardcoded), copies to the step-5 IPK, and removes the banner. No `finish()`/`stepPost()` change: the synced values are captured normally by `stepPost` and pushed by the existing `finish()` loop — so execution is inherently deferred to `finish()`. Verified live in the browser: Excel IPK 3.5 vs last-row PDDIKTI IPK 3.40 (20252, active) triggered the banner+button; typing in step-5 synced to the table and vice-versa; the button set last-row IPK to the Excel value, selected status Aktif (`A`), synced step-5, and removed the banner.
- **Changes:** The last academic row's IPK is now a single source shared two-way (realtime) with the step-5 graduation IPK input; Excel IPK is the initial default only. A warning + "Auto-update IPK & status ke Aktif" button appears when that last row is active and its IPK differs from the Excel IPK; when inactive the wizard only warns. The button stages the Excel IPK and Aktif status for the last row, executed at `finish()` via the existing per-semester push.

### ENH-007 — Academic table: only rows with a still-active semester are editable
- **Status:** `implemented`
- **Issue:** #101
- **Recorded:** 2026-08-30 15:18
- **Implemented:** 2026-08-30 17:45
- **Problem:** In the step 2 academic table (ENH-003 made all rows inline-editable), every semester row can be edited, including historical semesters that are no longer active. PDDIKTI only reports/accepts updates for the current active semester, so editing past rows is meaningless and risks pushing invalid corrections.
- **Possible Fix:** Disable inline editing for rows whose `id_semester` is not still-active in the Daftar Semester (semester reference list); only the row for an active semester remains editable. The reference list of active semesters must come from the same source used elsewhere (e.g. `getSemester()`).
- **Actual Fix:** In `Graduation::step()`, the `getSemester()` response (already fetched for `$semesterOptions`) is additionally filtered to build a set of active semester IDs where `a_periode_aktif` == '1' (verified live: the Daftar Semester currently marks three semesters active — 20252, 20253 Pendek, 20261 — and the set is mutable). Pass `$activeSemesters` to the view. In `wizard.php` card 2 table, a row is rendered editable (status `<select>` + `ips`/`ipk` inputs) ONLY when its `id_semester` is in `$activeSemesters`; every other row renders the same cells read-only (plain text). Editable state per selector is decided at implementation (native `disabled`/`readonly`; unaffected rows not submitted to `stepPost`, matching the failed rows being skipped anyway).
- **Actual Implemented:** In `Graduation::step()`, after building `$semesterOptions` and `$semesterRows` from `getSemester()`, a new `$activeSemesterIds` set is built from rows whose `a_periode_aktif` is truthy (`! empty`), and passed to the view as `activeSemesterIds`. In `wizard.php` card 2, each academic row computes `$isActive = in_array(id_semester, activeSemesterIds)` and applies ` disabled` to the status `<select>` and the `ips`/`ipk` inputs of non-active rows; non-editable columns are untouched (read-only text). Disabled inputs are not submitted to `stepPost`, so WizardProgress keeps prior state for inactive semesters and `finish()` only pushes corrections for active ones.
- **Changes:** Only rows for currently-active semesters (`a_periode_aktif='1'`) in the Daftar Semester are inline-editable in the step 2 academic table; historical/non-active rows are rendered read-only (disabled). Verified live that the active set is 20252, 20253 Pendek, 20261 — and that it mutates.

### ENH-008 — Auto-check step checkboxes; all checkboxes must be checked to proceed
- **Status:** `implemented`
- **Issue:** #102
- **Recorded:** 2026-08-30 15:18
- **Implemented:** 2026-08-30 20:40
- **Problem:** Wizard steps currently lack a clear manual-verification gate structure. The admin can advance without having explicitly confirmed each verification area, and the wizard does not auto-confirm conditions that are already objectively satisfiable (name match, IPK+status, transcript inclusion).
- **Possible Fix:** Give every step a checkbox that must be checked before advancing to the next verification step. Auto-check where the criterion is objectively verifiable: **step 1** checked when the KTP name (from Excel, per ENH-005) exactly equals the PDDIKTI registered name; **step 2** checked when the last-semester IPK matches the Excel IPK AND the status is Aktif; **step 2b** checked when a thesis/skripsi MK exists with a grade AND `choosed:true` (per BUG-001); **step 3** cannot be auto-checked. Every checkbox may still be checked manually regardless of the auto-check condition. Progression requires all checkboxes checked.
- **Actual Fix:** Add a checkbox to each verification card in `wizard.php`: step 1 `identity_ok` (exists), step 2 `academic_ok` (new), step 3/Kelengkapan Transkrip `transcript_ok` (new, after ENH-009 renumbering), step 4/PISN `pisn_ok` (exists; cannot auto-check). Server-side auto-check at render: step 1 checked when Excel `nama` is optionally present AND equals `identity['nama_mahasiswa']` (field verified live); step 2 checked when `academic` last row (`id_semester` largest) `ipk` === Excel IPK AND `id_status_mahasiswa` is Aktif; step 3 checked when `completeness` finds a thesis MK with a grade AND `choosed:true`. Every checkbox remains manually togglable regardless of the auto-check state. `stepPost` validation (`Graduation.php:367–376`) is extended to require all four checkboxes (`identity_ok`, `academic_ok`, `transcript_ok`, `pisn_ok`) before advancing; each flag is stored on `$student`. Auto-check state is informational pre-fill (not blocking manual override), matching the manual-verification design of the wizard.
- **Actual Implemented:** `Graduation::step()` computes three server-side auto-check flags before rendering: `$identityOk` (Excel `nama` non-empty AND exactly equals `identity['nama_mahasiswa']`), `$academicOk` (last academic row, largest `id_semester`, is active per ENH-007 AND its stored/live `ipk` `===` the Excel IPK AND its `id_status_mahasiswa` `===` the "Aktif" status code resolved from `$statusOptions`), and `$transcriptOk` (a thesis/skripsi MK exists with a non-empty grade AND `choosed:true`, from the BUG-001 completeness data). These flags plus `$activeCode` are passed to the view. `wizard.php` adds a checkbox per card: step 1 `identity_ok` (checked when `$identityOk` OR user-stored true), step 2 `academic_ok` (checked when `$academicOk` OR stored true), step 3 `transcript_ok` (checked when `$transcriptOk` OR stored true), step 4 `pisn_ok` (manual only). Every checkbox remains manually togglable regardless of auto-check. Step 2 additionally recomputes `academic_ok` in real time (vanilla JS): it is re-checked on page load and on `input` of either IPK field OR after the ENH-006 Auto-update button click, when the last-row IPK equals the Excel IPK AND the last-row status select equals the Aktif code. `Graduation::stepPost()` validation now requires all four checkboxes (`identity_ok`, `academic_ok`, `transcript_ok`, `pisn_ok`) before advancing; each is stored on `$student`. Two pre-existing display bugs were fixed as part of this (needed to surface the validation failures): `step()` now initializes `$error` from `session()->getFlashdata('graduation_error')` (the stepPost validation flash), and the API-error assignments guard with `empty($error)` so they do not overwrite an existing validation flash; `wizard.php` renders it. Verified live via Playwright on NIM 202010087: name-mismatch student shows step 1 unchecked + step 3 auto-checked (thesis Skripsi exists + grade A + choosed:true) + step 2 unchecked until the Auto-update button is clicked (then re-checked in real time without submit); pressing "Berikutnya" with checkboxes unchecked is rejected with the exact aggregated validation messages; with all four checked it advances to the preview; session cancelled with no Neo Feeder mutation.
- **Changes:** `app/Controllers/Graduation.php` (`step()` computes `$identityOk`/`$academicOk`/`$transcriptOk`/`$activeCode` + passes to view; reads flash `graduation_error` and guards API errors; `stepPost()` requires + stores all four checkbox flags), `app/Views/graduation/wizard.php` (adds `academic_ok` + `transcript_ok` checkboxes; auto-check on submit checks; step 2 real-time JS recompute).

### ENH-009 — Simplify step numbering (whole numbers, no 2b)
- **Status:** `implemented`
- **Issue:** #103
- **Recorded:** 2026-08-30 15:18
- **Implemented:** 2026-08-30 16:20
- **Problem:** The wizard uses `2b` as a step label (Kelengkapan Transkrip card is currently numbered as a sub-step of the academic step), which looks inconsistent alongside whole-numbered steps 1, 2, 3, 4.
- **Possible Fix:** Two options were considered: (a) remove step numbering entirely, or (b) renumber all steps to whole numbers with no sub-step. **Decision (AGENT): option (b)** — renumber the transcript card from `2b` to its own whole number (2b → 3, and 3/4 shift accordingly), preserving the wizard's "which step am I on" orientation without sub-numbering.
- **Actual Fix:** In `wizard.php` card headers (lines 34, 58, 116, 175, 190) renumber to: `1. Identitas (cocok dengan KTP)`, `2. Akademik (status, IPK, SKS)`, `3. Kelengkapan Transkrip`, `4. Eligibilitas PISN`, `5. Input Kelulusan`. No other structural change; the transcript card becomes its own whole-numbered step. Any step references in ENH-008 (checkbox labels) and in the Upload/preview views must follow the same renumbering. (Verified the current labels: `1.`, `2.`, `2b.`, `3.`, `4.`.)
- **Actual Implemented:** Renumbered the five wizard card headers in `app/Views/graduation/wizard.php` to whole numbers: `1. Identitas`, `2. Akademik`, `3. Kelengkapan Transkrip`, `4. Eligibilitas PISN`, `5. Input Kelulusan` (the former `2b. Kelengkapan Transkrip` became `3.`, and the old `3.`/`4.` shifted to `4.`/`5.`). No structural or logic change; the card order is unchanged. Verified no other view references step numbers (upload/preview render none).
- **Changes:** Wizard step sequence is now a clean `1..5` with no sub-numbered step; the transcript card is its own whole-numbered step.

### ENH-010 — Fill in a missing thesis grade at the end of the wizard
- **Status:** `implemented`
- **Issue:** #110
- **Recorded:** 2026-08-30 23:37
- **Implemented:** 2026-08-31 12:03
- **Problem:** During transcript-completeness verification (card 3), a thesis/skripsi course can be present in the transcript but still carry no grade (`nilai_huruf` empty). The wizard currently only detects and displays this state (BUG-001 / `transcript_ok`) and blocks completeness, but it offers the admin no way to fill the missing thesis grade through the application.
- **Possible Fix:** Add the ability to update the missing thesis grade at the end of the process (in `finish()`, after all data is verified). The final thesis grade comes from a new column in the Excel template that holds a **letter grade** (A/B/C etc.) — one value per student. On card 3 (Kelengkapan Transkrip) the thesis course row of the detail table shows that grade (table sorted smallest→largest semester; the thesis is on the last semester). At `finish()` the letter grade is pushed to Neo Feeder **only when the thesis grade is currently missing** (`nilai_huruf` empty); the Excel value is used to fill it in and no push happens when a grade already exists. Only `nilai_huruf` is pushed. Target endpoint per USER direction: the `UpdateNilaiPerkuliahanKelas` act, filtered to update only this student on the last semester in the thesis class — its exact schema/filter must be verified live before implementation.
- **Actual Fix:** Add a `nilai_skripsi` column (letter grade, e.g. A/B/C) to the Excel template — added to `EXCEL_HEADERS`, `downloadTemplate()`, and `parseExcel()` (currently `nim`/`nama`/`tgl_keluar`/`ipk`). On card 3 (Kelengkapan Transkrip), for a detected thesis course row whose `hasGrade == false`, render the Excel `nilai_skripsi` value as the grade and store it on the student's WizardProgress state. At `finish()`, push the grade to Neo Feeder via the `UpdateNilaiPerkuliahanKelas` act **only when** the thesis row still has no grade (`nilai_huruf` empty) and `nilai_skripsi` is non-empty. `UpdateNilaiPerkuliahanKelas` (verified live via GetDictionary) takes a composite key `id_registrasi_mahasiswa` + `id_kelas_kuliah`, and a `record` with `nilai_huruf` (character(3)), `nilai_angka` (numeric(4,1)), `nilai_indeks` (numeric(4,2)) — both key values (`id_reg_pd` = id_registrasi_mahasiswa, `id_kls` = id_kelas_kuliah) are available directly on the cloud `nilai_mahasiswa` thesis row (verified live: `id_kls 81251467-...`, `id_reg_pd 88a105f4-...` for NIM 202010087), so no extra lookup is needed. Only `nilai_huruf` is sent per scope; confirm at implementation whether the endpoint requires `nilai_angka`/`nilai_indeks` alongside it.
- **Actual Implemented:** Added a `nilai_skripsi` column (letter grade) to the Excel template — `EXCEL_HEADERS`, `downloadTemplate()` (header + example `A`), and `parseExcel()` (stored uppercased on each student record). Added `NeoFeeder::updateNilaiPerkuliahanKelas($token, $idRegistrasi, $idKelas, $record)` using `sendMutation('UpdateNilaiPerkuliahanKelas', ...)` with composite key `id_registrasi_mahasiswa` + `id_kelas_kuliah` (key schema verified live via GetDictionary: key `id_registrasi_mahasiswa` + `id_kelas_kuliah`, record `nilai_huruf`/`nilai_angka`/`nilai_indeks`). In `Graduation::finish()`, for each saved student with a non-empty `nilai_skripsi`, the thesis/skripsi course is re-resolved via `getCekTranskripMahasiswa`; a new private helper `findMissingGradeThesis()` finds the thesis row whose grade is currently missing (`hasGrade == false` — `nilai_huruf`/`nilai_angka` empty) and returns its `id_reg_pd`/`id_kls` from the cloud row (both verified live on real data), then `updateNilaiPerkuliahanKelas` is called with only `record.nilai_huruf` = the Excel value. The push happens only when the grade is still missing; noise results are reported in the finish guidance. In `wizard.php` card 3, a thesis row without a grade now shows the Excel value with a "(akan diisi)" tag instead of a bare "Belum ada nilai" when a value was provided.
- **Changes:** The Excel template now carries an optional `nilai_skripsi` letter-grade column. A registered-but-ungraded thesis/skripsi course is filled in with the Excel letter grade at submission (`finish()`), only when it currently has no grade; students without a value in that column are left untouched. Card 3 indicates which thesis rows will be filled in.
- **Changes:** The Excel template now carries an optional `nilai_skripsi` letter-grade column. A registered-but-ungraded thesis/skripsi course is filled in with the Excel letter grade at submission (`finish()`), only when it currently has no grade; students without a value in that column are left untouched. Card 3 indicates which thesis rows will be filled in.

### ENH-011 — Verify and fix last-semester IPK in bulk via Excel
- **Status:** `implemented`
- **Issue:** #115
- **Recorded:** 2026-08-31
- **Implemented:** 2026-08-31
- **Problem:** Several (possibly all) students have a last-semester `ipk` that is not up to date in Neo Feeder. The graduation wizard only surfaces the last-semester IPK for the students being processed one at a time; there is no dedicated screen to bulk-verify and correct the last-semester IPK across many students against an external source.
- **Possible Fix:** Add a dedicated read-only "Verifikasi IPK" menu that takes an Excel file (Graduation template: `nim` + `ipk` columns), reads each student's last-semester `ipk` from Neo Feeder (`getAktivitasKuliahMahasiswa`, largest `id_semester`), shows a comparison (Neo Feeder IPK vs Excel IPK, cocok/beda) batch-by-batch, lets the admin tick the rows to fix, and pushes `updatePerkuliahanMahasiswa(['ipk' => ...])` only to the **active** last semester (`a_periode_aktif=1`, ENH-007 rule). State stored in a cache + resume cookie separate from the graduation `WizardProgress` so the two do not collide. Processing is batch-by-batch (one `getAktivitasKuliahMahasiswa` call per student) to avoid the 60 s PHP time limit.
- **Actual Fix:** Added `VerifikasiIpk` controller (`/verifikasi-ipk`, batch-by-batch comparison) + `VerifikasiIpkStore` library (cache prefix `ipk_verif_`, resume cookie `ipk_verif_resume`), routes, sidebar menu, and views (`index`/`verification`/`results`). The push uses a full `UpdatePerkuliahanMahasiswa` record — the whole active last-semester row from `getAktivitasKuliahMahasiswa` (minus the key) plus `id_pembiayaan` resolved via `GetListRiwayatPendidikanMahasiswa` and the `ipk` overridden with the Excel value — because Neo Feeder rejects partial records (`id_status_mahasiswa`, `biaya_kuliah_smt`, `id_pembiayaan` all demanded even though the GetDictionary schema lists only `id_status_mahasiswa` as NOT NULL). Per-row results (success/failure with the Neo Feeder error message) are shown on the `results` page. `apply()` uses `set_time_limit(0)` for large batches.
- **Actual Implemented:** 2026-08-31
- **Changes:** New `app/Controllers/VerifikasiIpk.php`, `app/Libraries/VerifikasiIpkStore.php`, `app/Views/verifikasi_ipk/*`; `NeoFeeder.php` gained `getListRiwayatPendidikanMahasiswa()`; routes + sidebar menu added.

### DOC-001 — Determine the fate of ARCHITECTURE.md & STRUCTURE.md (commit the pending documentation updates)
- **Status:** `verified`
- **Issue:** —
- **Recorded:** 2026-08-31 15:42
- **Implemented:** —
- **Problem:** `ARCHITECTURE.md` and `STRUCTURE.md` carry uncommitted changes left over from earlier sessions — they document features that were already implemented and merged (PISN routes and controllers `Mahasiswa`, `AktivitasKuliah`, `MahasiswaLulusDo`, `Graduation`; the `Auth`/`NeoFeeder`/`PisnService`/`WizardProgress` libraries; the graduation wizard flow and the wizard-resume cookie), yet they were deliberately left unstaged so they now drift from the actual code.
- **Possible Fix:** Stage `ARCHITECTURE.md` + `STRUCTURE.md` and commit them atomically with a conventional `docs:` message, ending the deliberate hold and re-syncing the documentation with the current code.
- **Actual Fix:** Commit the two files **as-is** (they already document the merged PISN features and match the code). Stage `ARCHITECTURE.md` + `STRUCTURE.md` from the saved stash and commit them atomically with a conventional `docs:` message. The docs are committed exactly as they stand — they are NOT extended to also cover the newer `VerifikasiIpk` controller/store/routes (out of scope for this item).
- **Actual Implemented:** —
- **Changes:** —

### DOC-002 — Update README to reflect the current project state, including a record of the Neo Feeder API endpoints used (focus GetDictionary)
- **Status:** `verified`
- **Issue:** —
- **Recorded:** 2026-08-31 15:42
- **Implemented:** —
- **Problem:** `README.md` does not yet reflect the current project state (menus/features: Mahasiswa, AktivitasKuliah, MahasiswaLulusDo, the PISN Graduation wizard, Verifikasi IPK; updated dependencies), and its API section documents only `GetToken`. The other Neo Feeder endpoints the project consumes are not recorded, so adding a new feature later requires re-testing endpoints that are already in use, and the `GetDictionary` endpoint (whose `fungsi` parameter reveals the actual `key`/`record` schema of a WS function, and which we already use to inspect schemas) is not captured.
- **Possible Fix:** Extend `README.md` — (1) align the feature/status summary and directory structure with the current state; (2) add a record of the Neo Feeder endpoints the project actually uses: per endpoint the request (act/token/params) and the response, including a dedicated `GetDictionary` subsection (payload `{'act':'GetDictionary','token':...,'fungsi':'<Fungsi>'}` with an example schema response) so new endpoints can be vetted without re-testing existing ones.
- **Actual Fix:** In `README.md`: (1) update the feature/status summary and Development Status to reflect the current menus (Mahasiswa, AktivitasKuliah, MahasiswaLulusDo, PISN Graduation wizard, Verifikasi IPK) and dependencies; (2) replace the current minimal API section with a record of the Neo Feeder endpoints the project actually consumes, per endpoint noting the WS act (or cloud path), request params (act/token and, where used, filter/order/key/record), and a concise response summary (error_code/data). Include a dedicated `GetDictionary` subsection documenting it as a schema-inspection tool called ad-hoc (not part of the app's runtime request path): payload `{'act':'GetDictionary','token':...,'fungsi':'<NamaFungsi>'}` and a short example schema response; the endpoint list is derived from the methods in `app/Libraries/NeoFeeder.php` (WS acts: GetToken, GetProfilPT, GetListMahasiswa, GetAktivitasKuliahMahasiswa, GetListRiwayatPendidikanMahasiswa, GetStatusMahasiswa, GetJenisKeluar, GetSemester, GetListMahasiswaLulusDO, GetCountMahasiswa, GetCountAktivitasKuliahMahasiswa, GetCountMahasiswaLulusDO, GetTranskripMahasiswa, GetBiodataMahasiswa, GetDetailPerkuliahanMahasiswa, InsertMahasiswaLulusDO, Insert/Update/Delete BiodataMahasiswa, Insert/Update/Delete PerkuliahanMahasiswa, UpdateNilaiPerkuliahanKelas; plus cloud REST `/ws/transkrip/cari_mahasiswa` + `/ws/transkrip/nilai_mahasiswa`). Schema facts are summarized from prior live-verified test results (already recorded in the tracker), not re-tested.
- **Actual Implemented:** —
- **Changes:** —
