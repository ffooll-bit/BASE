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
- **Status:** `recorded` | `verified` | `rejected` | `implemented`
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

### DOC-001 — ARCHITECTURE.md environment table is wrong
- **Status:** `implemented`
- **Issue:** #25
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The environment variable table in `ARCHITECTURE.md` lists `neofeeder.api_url`, `neofeeder.timeout`, and `neofeeder.ttl`, which do not match the actual config (`app/Config/NeoFeeder.php`). A contributor following the documentation sets variables that the application ignores, so the Neo Feeder connection falls back to defaults silently.
- **Possible Fix:** Update the env var table in `ARCHITECTURE.md` to match the four variables actually used by `app/Config/NeoFeeder.php`: `neofeeder.apiBaseUrl`, `neofeeder.connectionTimeout`, `neofeeder.requestTimeout`, `neofeeder.validationTTL`.
- **Actual Fix:** Rewrite the env var table rows to the real keys: `neofeeder.apiBaseUrl`, `neofeeder.connectionTimeout`, `neofeeder.requestTimeout`, `neofeeder.validationTTL`.
- **Actual Implemented:** ARCHITECTURE.md env table corrected via PR #39.
- **Changes:** The env table now lists the real, working variable names; contributors following it set values the app actually uses.

### DOC-002 — `env` template missing `neofeeder.*` section
- **Status:** `implemented`
- **Issue:** #26
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** README and CONTRIBUTING instruct contributors to configure `neofeeder.*` in `.env`, but the `env` template has no such section. A contributor copying `env` to `.env` has no documented list of required variables, which is also why DOC-001 went unnoticed.
- **Possible Fix:** Add a `neofeeder.*` section to the `env` template documenting the four variables from `app/Config/NeoFeeder.php`, each with a short comment and a safe default.
- **Actual Fix:** Add a `neofeeder.*` section to `env` documenting the four overridable properties with commented defaults (`apiBaseUrl`, `connectionTimeout`, `requestTimeout`, `validationTTL`).
- **Actual Implemented:** `NEO FEEDER` section added to `env` template via PR #39.
- **Changes:** Copying `env` to `.env` now shows the four `neofeeder.*` variables with commented defaults; DOC-001 type drift becomes visible immediately.

### ENH-001 — `builds` file (Stability Toggle script)
- **Status:** `implemented`
- **Issue:** #17
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The CI4 starter "Stability Toggle" script sits at the repo root with no extension, so it looks like a directory next to the ignored `build/` folder. It is unused, and its presence confuses anyone browsing the repository layout.
- **Possible Fix:** Delete the `builds` file; nothing in the project references it.
- **Actual Fix:** Delete `builds` from the repo root (`git rm`).
- **Actual Implemented:** `builds` deleted via PR #30.
- **Changes:** Repo root no longer has an extensionless script file; `git grep "builds"` is clean.

### ENH-002 — `preload.php` sample
- **Status:** `implemented`
- **Issue:** #18
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** `preload.php` is an OPcache preload sample shipped with the starter and is not referenced anywhere in the project. It adds dead weight to the repository root and implies configuration that is not in place.
- **Possible Fix:** Delete `preload.php`; no configuration or code in the project depends on it.
- **Actual Fix:** Delete `preload.php` from the repo root (`git rm`).
- **Actual Implemented:** `preload.php` deleted via PR #30.
- **Changes:** Repo root no longer carries an unused OPcache sample; `git grep "preload"` is clean.

### ENH-003 — `package.json` name still `codeigniter4-appstarter`
- **Status:** `implemented`
- **Issue:** #19
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The project name in `package.json` is `codeigniter4-appstarter`, leaking the starter template, while `composer.json` identifies the project as `ffooll-bit/base`. The mismatch is visible in tooling output and confusing for contributors.
- **Possible Fix:** Rename the `name` field in `package.json` to `base`, matching the Composer package name.
- **Actual Fix:** Change `"name": "codeigniter4-appstarter"` to `"name": "base"` in `package.json`.
- **Actual Implemented:** `name` changed to `base` via PR #30.
- **Changes:** npm tooling now reports `base`; `npm run build` still works.

### ENH-004 — `composer.json` homepage case mismatch
- **Status:** `implemented`
- **Issue:** #20
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** `composer.json` sets `homepage` to `https://github.com/ffooll-bit/base` (lowercase), while the repository is `ffooll-bit/BASE`. The link is inconsistent with the actual repository URL.
- **Possible Fix:** Align the `homepage` URL casing in `composer.json` with the repository (`BASE`).
- **Actual Fix:** Change `"homepage": "https://github.com/ffooll-bit/base"` to `"homepage": "https://github.com/ffooll-bit/BASE"`.
- **Actual Implemented:** `homepage` casing fixed via PR #30.
- **Changes:** `composer.json` homepage matches the repository URL exactly.

### ENH-005 — CI job has no least-privilege `permissions`
- **Status:** `implemented`
- **Issue:** #27
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The CI workflow runs without a `permissions` block, so the job receives the default token permissions. Impact: the Actions token is granted more access than the job needs, widening the blast radius if a dependency or script is compromised.
- **Possible Fix:** Add `permissions: contents: read` to the job, limiting the token to read-only access to repository contents.
- **Actual Fix:** Add `permissions: contents: read` at the workflow level. GitHub docs + security guidance (GitGuardian, StepSecurity, Exercism) confirm least-privilege for read-only CI.
- **Actual Implemented:** `permissions: contents: read` added at workflow level via PR #31.
- **Changes:** The Actions token in CI is now read-only for repo contents; no other scopes granted.

### ENH-006 — CI has no `concurrency` control
- **Status:** `implemented`
- **Issue:** #28
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** Re-pushing to a PR starts a new CI run without cancelling the previous one. Impact: wasted Actions minutes and out-of-order results when multiple commits land in quick succession.
- **Possible Fix:** Add a `concurrency` group keyed on the PR/branch with `cancel-in-progress: true`.
- **Actual Fix:** Add at workflow level: `concurrency: group: ${{ github.workflow }}-${{ github.ref }}` with `cancel-in-progress: true` (GitHub docs pattern).
- **Actual Implemented:** `concurrency` group with `cancel-in-progress: true` added via PR #31.
- **Changes:** Re-pushes to a PR cancel the in-flight CI run for that ref instead of queueing parallel runs.

### ENH-007 — CI has no `timeout-minutes`
- **Status:** `implemented`
- **Issue:** #29
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The CI job has no `timeout-minutes`, so a hung step (e.g. a stuck composer/npm install) can run indefinitely and consume Actions minutes without finishing. Impact: builds never fail-closed and billable time accrues.
- **Possible Fix:** Add a sensible `timeout-minutes` to the job.
- **Actual Fix:** Add `timeout-minutes: 30` to the `build` job. GitHub default is 360; Exercism recommends ~30 for test workflows.
- **Actual Implemented:** `timeout-minutes: 30` added to the `build` job via PR #31.
- **Changes:** A CI job that hangs now fails after 30 minutes instead of running up to GitHub's 360-minute default.

### ENH-008 — Repo is `private` but planned to be public
- **Status:** `implemented`
- **Issue:** #23
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The repository is private, and GitHub returns 403 when configuring branch protection on a private free-tier repo. Impact: main cannot be protected (require PR, approvals, status checks), so the workflow guardrails documented in CONTRIBUTING are not enforced by the platform.
- **Possible Fix:** Publicize the repo when ready, then enable branch protection on `main`: require PRs, one approval, and passing status checks.
- **Actual Fix:** Publicize the repo via `gh repo edit --visibility public`, then create a branch protection rule on `main`: require PRs, one approval, status checks. Timing depends on when the repo is meant to go public.
- **Actual Implemented:** Repo set to PUBLIC on 2026-08-02 (`gh repo edit --visibility public`). Branch protection on `main` enabled: 1 required approval, strict `build` status check, enforce admins, force-push disabled, deletions disabled (F6).
- **Changes:** The repository is now public; `main` is protected — direct pushes and force-pushes are blocked, all merges require an approved PR with a green CI build.

### ENH-009 — No Dependabot configuration
- **Status:** `implemented`
- **Issue:** #24
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** There is no `.github/dependabot.yml`. Composer, npm, and GitHub Actions dependencies are never auto-checked for updates. Impact: outdated or vulnerable dependencies go unnoticed until a human checks manually.
- **Possible Fix:** Add `.github/dependabot.yml` covering `composer`, `npm`, and `github-actions`, with a weekly schedule.
- **Actual Fix:** Add `.github/dependabot.yml` with three `package-ecosystem` entries: `composer`, `npm`, `github-actions`, each `directory: "/"` and `schedule.interval: "weekly"`.
- **Actual Implemented:** `.github/dependabot.yml` added via PR #32.
- **Changes:** Dependabot now checks Composer, npm, and GitHub Actions dependencies weekly and opens update PRs automatically.

### ENH-010 — Extra merge methods enabled
- **Status:** `implemented`
- **Issue:** #21
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** The repository allows merge commits and rebase merges even though the workflow prefers squash merges. Impact: a contributor can merge a PR with a non-squash history, producing noisy or non-atomic history that contradicts CONTRIBUTING.
- **Possible Fix:** Disable `allow_merge_commit` and `allow_rebase_merge`; keep squash merge only.
- **Actual Fix:** Disable `allow_merge_commit` and `allow_rebase_merge` via `gh api -X PATCH` on the repo; keep squash merge only. GitHub docs confirm enforcing one method is supported.
- **Actual Implemented:** Set `allow_merge_commit=false` and `allow_rebase_merge=false` via `gh api -X PATCH` on the repo (F1).
- **Changes:** Only squash merge is now available; merge-commit and rebase-merge are disabled in repo settings.

### ENH-011 — `delete_branch_on_merge` is off
- **Status:** `implemented`
- **Issue:** #22
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-02
- **Problem:** Branches are not auto-deleted on merge. Impact: stale branches accumulate on the remote (as happened with PRs #8-#11), requiring manual cleanup and confusing `git branch -r` output.
- **Possible Fix:** Enable "Automatically delete head branches" in repository settings.
- **Actual Fix:** Set `delete_branch_on_merge=true` via `gh api -X PATCH` on the repo so GitHub auto-deletes head branches on merge.
- **Actual Implemented:** Set `delete_branch_on_merge=true` via `gh api -X PATCH` on the repo (F1).
- **Changes:** Head branches are auto-deleted on merge; stale remote branches no longer accumulate.

### ENH-012 — Local php-cs-fixer reports CRLF false positives on Windows
- **Status:** `implemented`
- **Issue:** #48
- **Recorded:** 2026-08-02
- **Implemented:** 2026-08-27
- **Problem:** Running `php-cs-fixer fix --dry-run` locally on Windows flags all PHP files (29 files) even though their content already conforms to the configured rules. Impact: the local dev cannot use the dry-run diff as a real signal, since every file is reported regardless of actual style violations. CI is unaffected (Linux checks out LF and passes), so this is purely a local-developer experience issue.
- **Possible Fix:** Option A (full): set `.editorconfig` `end_of_line = lf`, set `.gitattributes` to `* text=auto eol=lf`, run `git add --renormalize .` and re-checkout so Windows always checks out LF. Trade-off: produces a noisy diff touching every PHP file. Option B (minimal): change `.editorconfig` `end_of_line` to `lf` so editors stop writing CRLF, and document in CONTRIBUTING that Windows devs should set `git config --global core.autocrlf false`. Old CRLF files are still flagged once, but no new ones appear.
- **Actual Fix:** Applied Option A: `.editorconfig` `end_of_line = lf`, `.gitattributes` `* text=auto eol=lf`, `git add --renormalize .`, CI line-ending/BOM checks — all done in workflow-adoption PR #46. php-cs-fixer dry-run should now pass cleanly on Windows.
- **Actual Implemented:** Line-ending false positives resolved repo-wide via PR #46: `.editorconfig` `end_of_line = lf`, `.gitattributes` `* text=auto eol=lf`, `git add --renormalize .`, and CI `build` now checks CRLF line endings and UTF-8 BOM in `*.md`.
- **Changes:** Running `php-cs-fixer fix --dry-run` on Windows no longer flags every PHP file; CI guards against regression.

### DOC-003 — Inconsistent tracking of auto-generated docs (ARCHITECTURE.md committed, STRUCTURE.md untracked)
- **Status:** `implemented`
- **Issue:** #47
- **Recorded:** 2026-08-25
- **Implemented:** 2026-08-27
- **Problem:** Two Magic Context-generated documentation files have different git tracking states. `ARCHITECTURE.md` has been committed since 2024-07-17 (commit 7159a1d) and updated through multiple PRs (#1, #15, #37, #39, #46). `STRUCTURE.md` remains untracked. Both files have headers indicating they are auto-generated by Magic Context. Contributors don't know which generated files should be versioned vs. ephemeral.
- **Possible Fix:** Decide and document policy: (A) Track both — add STRUCTURE.md to git; (B) Track neither — add both to .gitignore, generate on demand; (C) Track ARCHITECTURE.md only as canonical architecture doc, treat STRUCTURE.md as ephemeral working file.
- **Actual Fix:** Policy decision: Option A — track both. Add STRUCTURE.md to git tracking. Both Magic Context-generated docs are versioned as project documentation.
- **Actual Implemented:** Policy decision Option A applied via PR #49: STRUCTURE.md added to git tracking. Both Magic Context-generated docs (ARCHITECTURE.md and STRUCTURE.md) are now versioned as project documentation.
- **Changes:** Both generated docs are tracked in git; contributors have a clear policy on which Magic Context files are versioned (both), removing the earlier inconsistency.

### ENH-013 — PISN student graduation flow (Excel upload + sequential manual verification)
- **Status:** `verified`
- **Issue:** #53
- **Recorded:** 2026-08-27
- **Implemented:** `—`
- **Problem:** Admin processes student graduation for PISN (Penomoran Ijazah dan Sertifikat Profesi Nasional / National Diploma & Professional Certificate Numbering) manually in Neo Feeder through a multi-step flow with strict verification gates. BASE has no structured interface guiding this flow, making it error-prone — especially because post-graduation data corrections (KTP/Nama/Jenis Kelamin) require formal correspondence with LLDIKTI. Evidence (KTP, transcript) is still in image form, so verification cannot be automated. Current manual flow (from the Neo Feeder admin's perspective): (1) verify identity — KTP, Nama, Jenis Kelamin via *Mahasiswa → Daftar Mahasiswa → search by NIM/NPM*; these three fields must be correct before graduation because post-graduation correction requires writing to LLDIKTI; (2) verify academics — *Perkuliahan → Aktifitas Kuliah Mahasiswa → find in the graduation semester period*; ensure active status that semester, IPK & SKS match the transcript; correct immediately, and editing data requires *Biaya Kuliah Semester* to be filled (not empty); (3) check eligibility in the PISN application — the PISN API is reported available but still awaiting confirmation from LLDIKTI admin; for now the admin checks manually; if not eligible, the data must be adjusted; (4) the graduation input — Neo Feeder → *Perkuliahan → Daftar Mahasiswa Lulus/Dropout → add student*, fill NPM, Nama, Jenis Keluar, Tanggal Keluar/Tanggal Lulus, Periode Keluar, IPK, and No Ijazah/No Sertifikat Profesi = "-"; (5) post-graduation — wait several days until data syncs to PISN, then generate the Ijazah number in the PISN web application.
- **Possible Fix:** Build a feature in BASE that mimics the Neo Feeder flow with two additions from user input: (a) **upload data via Excel** — admin uploads an Excel file containing prospective graduate student data; (b) **sequential manual verification** — because evidence (KTP, transcript, etc. still in image form) cannot be auto-verified, the admin still checks item by item, but the UI is made sequential with a **Next** button to advance to the next record. Step detail: (1) upload Excel → build a verification queue; (2) per-item identity verification wizard (KTP/Nama/Jenis Kelamin) via Daftar Mahasiswa, click Next per item; (3) per-item academic verification wizard (active status, IPK, SKS) via Aktifitas Kuliah Mahasiswa, with validation that Biaya Kuliah Semester is required when editing; (4) check PISN eligibility manually — **PISN API is deferred (manual stage first) while awaiting LLDIKTI confirmation, but the API integration scaffolding is prepared so it can be plugged in once available**; (5) graduation input via Daftar Mahasiswa Lulus/Dropout (NPM, Nama, Jenis Keluar, Tanggal Keluar/Lulus, Periode Keluar, IPK, No Ijazah/No Sertifikat Profesi = "-"); (6) post-graduation guidance (wait for PISN sync, generate Ijazah number in web PISN). Verification remains manual one by one; the system only eases navigation and input.
- **Actual Fix:** Feasible via NeoFeeder WS API (GetListMahasiswa, GetAktivitasKuliahMahasiswa, InsertMahasiswaLulusDO) + PISN. The PISN eligibility-check API exists (register via bit.ly/FormAPIPISN, docs emailed) — live integration is deferred; prepare scaffolding (service interface + adapter seam) so the API can be plugged in later. Excel upload aligns with PISN's own "Upload file excel" eligibility method. Implementation: add phpoffice/phpspreadsheet to parse Excel; build a sequential verification wizard (Excel upload → identity check → academic check with Biaya Kuliah Semester validation → PISN eligibility check manual/API → graduation input → post-grad guidance). Not a duplicate (BASE has no graduation feature).
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-014 — Standalone Neo Feeder menu pages used by the graduation flow
- **Status:** `implemented`
- **Issue:** #51
- **Recorded:** 2026-08-25
- **Implemented:** 2026-08-27
- **Problem:** The graduation flow (ENH-013) needs access to several Neo Feeder menus — *Daftar Mahasiswa*, *Aktifitas Kuliah Mahasiswa*, *Daftar Mahasiswa Lulus/Dropout*. BASE has no standalone menu pages mirroring Neo Feeder navigation, so the admin must switch to Neo Feeder for each verification/input step.
- **Possible Fix:** Add standalone menu pages in BASE mapping the Neo Feeder menus used in the ENH-013 flow: *Daftar Mahasiswa*, *Aktifitas Kuliah Mahasiswa*, *Daftar Mahasiswa Lulus/Dropout* (and any other menus that surface during ENH-013 implementation). Each page calls the NeoFeeder API through the existing service layer (`Libraries/NeoFeeder`). These become the navigation surface for the ENH-013 wizard.
- **Actual Fix:** Feasible — the NeoFeeder WS API exposes GetListMahasiswa, GetAktivitasKuliahMahasiswa, GetListMahasiswaLulusDO. Add read-only pages in BASE (controller + view + route) calling the `Libraries/NeoFeeder` layer extended with these three Get methods. These become the navigation surface for the ENH-013 wizard.
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-015 — CRUD operations for Neo Feeder entities (optional)
- **Status:** `verified`
- **Issue:** #52
- **Recorded:** 2026-08-27
- **Implemented:** `—`
- **Problem:** The menu pages (ENH-014) are initially read-only. Several graduation-flow steps (ENH-013) require changing data (e.g. correcting Aktifitas Kuliah Mahasiswa, inputting graduation), so edit/add/delete features are useful where the NeoFeeder API supports them.
- **Possible Fix:** Beyond the read-only menus (ENH-014), add create/edit/delete operations for NeoFeeder entities where the API supports them (check the NeoFeeder API documentation per endpoint). Marked **optional** by the user — can be done partially or deferred. Ensure it is built on top of ENH-014.
- **Actual Fix:** Feasible — the NeoFeeder WS API supports Insert/Update/Delete (InsertBiodataMahasiswa, InsertPerkuliahanMahasiswa, etc.). Add CRUD where the API permits. Optional — implement per endpoint availability, built on top of the ENH-014 pages.
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-016 — Graduation wizard progress resumable across auth-session expiry (no database)
- **Status:** `implemented`
- **Issue:** #55
- **Recorded:** 2026-08-27
- **Implemented:** 2026-08-27
- **Problem:** The PISN graduation wizard (ENH-013) verifies a batch manually, one student at a time (click Next per record). The current batch is 85 records (34 + 51). CI4 session lifetime is 7200s (2h, `app/Config/Session.php:53`) using the files driver in `writable/session`. Manual verification is slow (the admin must open KTP/transcript images and compare), so a single record can take long or the admin may be interrupted. If the auth session expires mid-wizard, the wizard state (current step index + per-record verification flags/notes) is lost and the admin must restart from the first record — a major usability and data-integrity risk for an 85-record batch.
- **Possible Fix:** Persist wizard progress independently of the auth session, in CI4 Cache (filesystem, `writable/cache` — already present), keyed by a resume token with a long TTL (e.g. 24h). On every Next click, write the progress entry (current step index + per-record flags/notes). When the auth session expires, the existing `prior_auth` cookie (24h, ARCHITECTURE.md) lets the Auth Filter detect the timeout and re-establish login; the wizard then reads progress from cache and resumes at the saved step instead of restarting. Introduces no local database — consistent with the thin-client architecture (`ARCHITECTURE.md`: "no local user database"; `Models/` is an empty placeholder). Audit trail (who verified what/when) is a separate, optional concern that would justify a small DB table and should be its own item if required.
- **Actual Fix:** Verified feasible — no new external dependency. CI4 session lifetime is 7200s (2h, `app/Config/Session.php:53`, files driver in `writable/session`), confirming the mid-wizard expiry risk for an 85-record (34+51) manual batch. The `prior_auth` cookie already exists (`app/Libraries/Auth.php:149/165/180`) and is used by the Auth Filter to detect session timeout and re-establish login — so resumption after re-login is supported by existing infra. CI4 Cache is a built-in framework service with a filesystem store already present at `writable/cache`; persisting wizard progress keyed by a resume token (long TTL, e.g. 24h) requires no new package. No existing wizard/resume/progress code was found (`grep` for wizard|resume|progress returned nothing) — so this is net-new, no duplication. Architecture preserved: no local database introduced, consistent with `ARCHITECTURE.md` ("no local user database") and empty `Models/`. Audit trail remains a separate optional concern (would justify a small DB table, tracked as its own item if wanted).
- **Actual Implemented:** Implemented via the code-implementation workflow: added `App/Libraries/WizardProgress` (CI4 Cache-backed, TTL 24h, key `wizard_progress_<token>`, methods generateToken/save/load/clear) registered as `Services::wizardProgress()`; added resume-token cookie helpers to `Auth` (`setWizardResumeCookie`/`getWizardResumeToken`/`clearWizardResumeCookie`, httpOnly, 24h, SameSite Lax) mirroring the existing `prior_auth` cookie. The resume token lives in a separate cookie that `AuthFilter` does not clear, so it survives the re-login after session expiry; the wizard UI (ENH-013) calls these. Project CI only checks `.md` line endings/BOM (ci.yml), so no `.php` concern beyond php-cs-fixer/phpunit.
- **Changes:** Verification progress for the graduation wizard can be persisted independently of the auth session; a wizard interrupted by the 2h session expiry resumes at the saved step after re-login instead of restarting the 85-record batch. No local database introduced.
