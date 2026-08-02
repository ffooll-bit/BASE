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
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** Running `php-cs-fixer fix --dry-run` locally on Windows flags all PHP files (29 files) even though their content already conforms to the configured rules. Impact: the local dev cannot use the dry-run diff as a real signal, since every file is reported regardless of actual style violations. CI is unaffected (Linux checks out LF and passes), so this is purely a local-developer experience issue.
- **Possible Fix:** Option A (full): set `.editorconfig` `end_of_line = lf`, set `.gitattributes` to `* text=auto eol=lf`, run `git add --renormalize .` and re-checkout so Windows always checks out LF. Trade-off: produces a noisy diff touching every PHP file. Option B (minimal): change `.editorconfig` `end_of_line` to `lf` so editors stop writing CRLF, and document in CONTRIBUTING that Windows devs should set `git config --global core.autocrlf false`. Old CRLF files are still flagged once, but no new ones appear.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`
