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
- **Status:** `recorded`
- **Issue:** —
- **Recorded:** 2026-08-30 02:21
- **Implemented:** —
- **Problem:** In `app/Views/graduation/wizard.php` the identity table (lines 41–43) and the academic table (lines 64–69) render every key returned by the Neo Feeder API, including columns whose values are random UUID strings (e.g. `id_registrasi_mahasiswa`, `id_mahasiswa`, `id_aktivitas_kuliah`). These columns are meaningless to the admin and clutter the verification UI. The same pattern exists in the Mahasiswa, Aktivitas Kuliah, and Mahasiswa Lulus-DO menus, but the current scope is PISN Graduation only.
- **Possible Fix:** In `wizard.php`, suppress columns whose values are UUID/random strings from both the identity and academic tables. Keep functional, non-random columns such as `id_semester`. Implement an explicit allow/deny list of column keys to hide. Other menus stay out of scope for now.
- **Actual Fix:** —
- **Actual Implemented:** —
- **Changes:** —

### ENH-002 — Sort academic table by id_semester ascending
- **Status:** `recorded`
- **Issue:** —
- **Recorded:** 2026-08-30 02:21
- **Implemented:** —
- **Problem:** `Graduation::step()` calls `getAktivitasKuliahMahasiswa` without an order parameter (`app/Controllers/Graduation.php:193–199`), so the academic table (card 2) displays rows in the raw API response order rather than chronological semester order, making the academic-history review hard to read.
- **Possible Fix:** Sort the academic rows by `id_semester` ascending (20261 → 20262 …). Decide between a PHP `usort` on the returned array versus passing an `order` parameter to the API; confirm during verification which approach is reliable.
- **Actual Fix:** —
- **Actual Implemented:** —
- **Changes:** —

### ENH-003 — Remove two unused inputs; make academic table inline-editable, stored in wizard
- **Status:** `recorded`
- **Issue:** —
- **Recorded:** 2026-08-30 02:21
- **Implemented:** —
- **Problem:** `wizard.php` lines 73–81 render a "Catatan akademik (opsional)" textarea (`academic_flag`) and a "Biaya Kuliah Semester" input (`biaya_kuliah`). `Graduation::stepPost` reads and validates them (`Graduation.php:286,288,312–314`) but `finish()` never sends them to Neo Feeder, so they are collected yet unused. The user instead wants the academic-history table (status, IPK, IPS per semester) in step 2 to be inline-editable.
- **Possible Fix:** Remove the `academic_flag` and `biaya_kuliah` fields (view + controller read/validation). Make the academic table rows editable inline (status, IPK, IPS columns). Store the corrected values in the `WizardProgress` state and submit them at `finish()` together with the graduation data (not pushed directly to Neo Feeder). The endpoint/record schema for sending academic edits must be confirmed against the WS guide during implementation.
- **Actual Fix:** —
- **Actual Implemented:** —
- **Changes:** —

### BUG-001 — Transcript completeness check must use "Cek Transkrip Mahasiswa" menu
- **Status:** `recorded`
- **Issue:** —
- **Recorded:** 2026-08-30 02:21
- **Implemented:** —
- **Problem:** `Graduation::step()` and `checkTranscriptCompleteness` (`Graduation.php:201–211, 251–269`) assess completeness from `getTranskripMahasiswa` and detect the thesis by course name. This is inaccurate: a thesis/skripsi course may already have a grade but not yet be flagged "included in transcript", so the current logic can wrongly report the transcript as incomplete or miss it.
- **Possible Fix:** Replace the data source with the Neo Feeder menu "Perkuliahan > Cek Transkrip Mahasiswa", which exposes a per-course marker indicating inclusion in the transcript. Discover the correct API endpoint (Playwright inspection of the Neo Feeder page is permitted) and read its response schema (the inclusion-marker field). Re-implement `checkTranscriptCompleteness` against that endpoint.
- **Actual Fix:** —
- **Actual Implemented:** —
- **Changes:** —

### ENH-004 — Format all wizard inputs to match Neo Feeder display
- **Status:** `recorded`
- **Issue:** —
- **Recorded:** 2026-08-30 02:21
- **Implemented:** —
- **Problem:** All inputs across the graduation wizard are not formatted to match how Neo Feeder displays/accepts the data — notably step 4 "Input Kelulusan" (`nim`, `nama`, `jenis_keluar`, `tgl_keluar`, `periode_keluar`, `ipk`, `no_ijazah` in `wizard.php:120–138`) and the academic inputs from ENH-003. Date, numeric value, period, and `jenis_keluar` representations differ from Neo Feeder's.
- **Possible Fix:** Adjust input formatting/validation across the whole wizard so values match Neo Feeder's representation (date format, period format, `jenis_keluar` allowed values, numeric/IPK formatting, etc.). Inspect Neo Feeder input types directly (Playwright permitted) to confirm the exact formats. Applies to every input in the wizard.
- **Actual Fix:** —
- **Actual Implemented:** —
- **Changes:** —
