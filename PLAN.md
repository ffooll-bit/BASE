# Project Preparation Plan — Agent-Ready Development

## Filosofi

Agent AI bekerja dalam siklus: **Baca Konteks → Rencana → Eksekusi → Verifikasi → Ulangi**.
Setiap file yang kita buat adalah bagian dari *cognitive architecture* agent — menjawab pertanyaan spesifik tanpa agent perlu menebak:

| Agent bertanya | Dijawab oleh |
|---|---|
| Siapa saya, bagaimana saya bersikap? | `AGENTS.md` |
| Langkah pertama apa yang harus saya lakukan? | `CONTRIBUTING.md` |
| Bagaimana sistem ini bekerja? | `ARCHITECTURE.md` |
| Seperti apa kode yang "benar" di sini? | `CODING_STANDARDS.md` |
| Seperti apa UI yang "benar" di sini? | `DESIGN.md` |
| Apa yang sudah berubah sebelumnya? | `CHANGELOG.md` |
| Apa yang harus saya cek sebelum PR? | `PULL_REQUEST_TEMPLATE.md` |
| Apakah kode saya melanggar aturan? | CI workflow (`ci.yml`) |
| Apakah kode saya masih bekerja? | Test suite |
| Apa yang harus saya kerjakan selanjutnya? | `OPEN_ISSUES.md` |

---

## Step 1: AGENTS.md — Agent Identity & Behavioral Rules

**Tujuan:** Agent tahu persis bagaimana harus bersikap, tanpa perlu inference.

**Isi kunci:**
- Persona: Senior developer yang efisien (lazy), profesional, dan tanggung jawab
- Golden rules: YAGNI, test sebelum PR, self-review wajib, komentar berkualitas
- Decision framework: Urutan prioritas (security > correctness > performance > aesthetics)
- Communication: Bahasa Indonesia untuk konteks lokal, Inggris untuk teknis murni
- Cross-session memory via `.memory/memory.yaml`

**Output:** `AGENTS.md` di root project.

---

## Step 2: CONTRIBUTING.md — Development Workflow

**Tujuan:** Agent tahu urutan langkah yang benar tanpa melompat-lompat.

**Isi kunci:**
- Branching: `feature/*`, `fix/*`, `chore/*`, `docs/*` dari `main`. Satu branch = satu tujuan.
- Commit: Conventional Commits (`feat:`, `fix:`, `chore:`, `docs:`, `refactor:`).
- Workflow siklus:
  1. Baca task → pahami requirement
  2. Planning: baca ARCHITECTURE, DESIGN, CODING_STANDARDS
  3. Branch → implement → self-review pakai checklist PR
  4. `php -l` semua file + `npm run build`
  5. Commit → push → PR
  6. Tunggu CI → fix jika merah
- Self-review checklist: debug code, magic numbers, duplikasi, security, escaping
- Definisi "Done": Code written, linted, verified, committed, PR sent.

**Output:** `CONTRIBUTING.md` di root project.

---

## Step 3: ARCHITECTURE.md — System Blueprint

**Tujuan:** Agent paham sistem tanpa harus membaca semua source code.

**Isi kunci:**
- Tech stack + rationale: CI4, AdminLTE 4, vanilla JS
- Request lifecycle (mermaid diagram)
- Auth flow detail (mermaid sequence diagram dari login sampai token validation)
- Dependency injection chain: `Services::auth()` → `Auth(NeoFeeder, Session, Encryption)`
- Directory structure dengan penjelasan tiap direktori
- Key classes & responsibilities (tabel)
- Asset pipeline: `npm install && npm run build` → `public/`

**Output:** `ARCHITECTURE.md` di root project.

---

## Step 4: DESIGN.md — UI/UX Consistency

**Tujuan:** Semua halaman terlihat seperti satu produk, bukan hasil 3 agent berbeda.

**Isi kunci:**
- Layout anatomy: header → sidebar → content-wrapper → footer
- Component patterns: Cards, Forms, Tables, Alerts
- Navigation: Sidebar menu, active state, submenu collapse
- Icon conventions: Font Awesome 6 (`fas` vs `far`)
- Responsive behavior: Breakpoints, sidebar collapse
- Referensi visual: Link ke AdminLTE 4 docs

**Output:** `DESIGN.md` di root project.

---

## Step 5: CODING_STANDARDS.md + Tooling — Code Style & Quality

**Tujuan:** Agent menulis kode yang seragam tanpa review manual.

**Isi kunci:**
- **PHP:** PSR-12, type hints wajib, CI4 conventions (`service()`), no `var_dump/dd`
- **JavaScript:** Vanilla JS, `addEventListener`, Bootstrap 5 data attributes
- **Views:** `esc()` wajib untuk output, alternate syntax (`<?php if (): ?>`), 4 spasi indentasi
- **CSS:** Bootstrap utility-first, BEM untuk custom classes
- **Tooling:**
  - `.php-cs-fixer.dist.php` atau `phpcs.xml` untuk PHP CS Fixer
  - `.editorconfig` untuk indentasi, charset, line endings seragam

**Output:**
- `CODING_STANDARDS.md` di root project
- `.php-cs-fixer.dist.php`
- `.editorconfig`

---

## Step 6: CHANGELOG.md — Release History

**Tujuan:** Agent tahu apa yang sudah berubah tanpa baca git log.

**Isi:**
- Format Keep a Changelog
- `[Unreleased]` section
- Entry pertama: Login Feature + AdminLTE Migration
- Agent update `[Unreleased]` setiap commit perubahan berarti

**Output:** `CHANGELOG.md` di root project.

---

## Step 7: PULL_REQUEST_TEMPLATE.md — Self-Review Gate

**Tujuan:** Agent memeriksa pekerjaannya sendiri sebelum PR.

**Template mencakup:**
- Deskripsi perubahan
- Jenis perubahan (feat/fix/refactor/chore/docs)
- Checklist self-review: debug code, validasi, escaping, duplikasi, screenshot
- `php -l` dan `npm run build` verification

**Output:** `.github/PULL_REQUEST_TEMPLATE.md`

---

## Step 8: CI Workflow — Automated Safety Net

**Tujuan:** Agent tidak perlu mikir "apa yang harus saya cek?" — CI yang cek.

**Isi:**
- Trigger: push & pull_request ke main
- Jobs: Setup PHP 8.2 + Node 20
- Steps: `composer install`, `npm ci`, `npm run build`, `php -l`, `phpunit`

**Output:** `.github/workflows/ci.yml`

---

## Step 9: OPEN_ISSUES.md — Backlog & Task Tracking

**Tujuan:** Agent tahu persis apa yang harus dikerjakan selanjutnya.

**Format:**
```markdown
# Open Issues / Backlog

## [P-1] Nama Fitur Besar
- [ ] TASK-001: Sub-task
- [ ] TASK-002: Sub-task
- Status: ...

## Known Issues
- ...
```

**Output:** `OPEN_ISSUES.md` di root project.

---

## Step 10: Init Tests — Agent Confidence Check

**Tujuan:** Agent bisa run `vendor/bin/phpunit` dan langsung tahu apakah kodenya masih berfungsi.

**Minimal test:**
- `AuthTest` — login success, login failed (mocked NeoFeeder), isLoggedIn, logout
- `NeoFeederTest` — getToken parsing, error handling, connection failure
- `AuthFilterTest` — redirect when not logged in, passthrough when logged in

Gunakan `CIUnitTestCase` + mocking. Buat agent punya feedback loop: ubah kode → run test → tahu apakah rusak.

**Output:** File test di `tests/`.

---

## Ringkasan

| Step | Output | Untuk Agent |
|------|--------|-------------|
| 1 | `AGENTS.md` | Identitas — siapa dia, bagaimana bersikap |
| 2 | `CONTRIBUTING.md` | Workflow — urutan langkah yang benar |
| 3 | `ARCHITECTURE.md` | World model — bagaimana sistem bekerja |
| 4 | `DESIGN.md` | Estetika — seperti apa UI yang benar |
| 5 | `CODING_STANDARDS.md` + configs | Kebiasaan — bagaimana menulis kode |
| 6 | `CHANGELOG.md` | Memori — apa yang sudah berubah |
| 7 | `.github/PULL_REQUEST_TEMPLATE.md` | Self-review — cek sebelum selesai |
| 8 | `.github/workflows/ci.yml` | Proofreader otomatis |
| 9 | `OPEN_ISSUES.md` | Prioritas — apa yang dikerjakan next |
| 10 | Unit tests | Confidence check — apakah kode bekerja |

Eksekusi berurutan dari Step 1 ke Step 10.
