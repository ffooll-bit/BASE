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
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The environment variable table in `ARCHITECTURE.md` lists `neofeeder.api_url`, `neofeeder.timeout`, and `neofeeder.ttl`, which do not match the actual config (`app/Config/NeoFeeder.php`). A contributor following the documentation sets variables that the application ignores, so the Neo Feeder connection falls back to defaults silently.
- **Possible Fix:** Update the env var table in `ARCHITECTURE.md` to match the four variables actually used by `app/Config/NeoFeeder.php`: `neofeeder.apiBaseUrl`, `neofeeder.connectionTimeout`, `neofeeder.requestTimeout`, `neofeeder.validationTTL`.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### DOC-002 — `env` template missing `neofeeder.*` section
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** README and CONTRIBUTING instruct contributors to configure `neofeeder.*` in `.env`, but the `env` template has no such section. A contributor copying `env` to `.env` has no documented list of required variables, which is also why DOC-001 went unnoticed.
- **Possible Fix:** Add a `neofeeder.*` section to the `env` template documenting the four variables from `app/Config/NeoFeeder.php`, each with a short comment and a safe default.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-001 — `builds` file (Stability Toggle script)
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The CI4 starter "Stability Toggle" script sits at the repo root with no extension, so it looks like a directory next to the ignored `build/` folder. It is unused, and its presence confuses anyone browsing the repository layout.
- **Possible Fix:** Delete the `builds` file; nothing in the project references it.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-002 — `preload.php` sample
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** `preload.php` is an OPcache preload sample shipped with the starter and is not referenced anywhere in the project. It adds dead weight to the repository root and implies configuration that is not in place.
- **Possible Fix:** Delete `preload.php`; no configuration or code in the project depends on it.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-003 — `package.json` name still `codeigniter4-appstarter`
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The project name in `package.json` is `codeigniter4-appstarter`, leaking the starter template, while `composer.json` identifies the project as `ffooll-bit/base`. The mismatch is visible in tooling output and confusing for contributors.
- **Possible Fix:** Rename the `name` field in `package.json` to `base`, matching the Composer package name.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-004 — `composer.json` homepage case mismatch
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** `composer.json` sets `homepage` to `https://github.com/ffooll-bit/base` (lowercase), while the repository is `ffooll-bit/BASE`. The link is inconsistent with the actual repository URL.
- **Possible Fix:** Align the `homepage` URL casing in `composer.json` with the repository (`BASE`).
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-005 — CI job has no least-privilege `permissions`
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The CI workflow runs without a `permissions` block, so the job receives the default token permissions. Impact: the Actions token is granted more access than the job needs, widening the blast radius if a dependency or script is compromised.
- **Possible Fix:** Add `permissions: contents: read` to the job, limiting the token to read-only access to repository contents.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-006 — CI has no `concurrency` control
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** Re-pushing to a PR starts a new CI run without cancelling the previous one. Impact: wasted Actions minutes and out-of-order results when multiple commits land in quick succession.
- **Possible Fix:** Add a `concurrency` group keyed on the PR/branch with `cancel-in-progress: true`.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-007 — CI has no `timeout-minutes`
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The CI job has no `timeout-minutes`, so a hung step (e.g. a stuck composer/npm install) can run indefinitely and consume Actions minutes without finishing. Impact: builds never fail-closed and billable time accrues.
- **Possible Fix:** Add a sensible `timeout-minutes` to the job.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-008 — Repo is `private` but planned to be public
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The repository is private, and GitHub returns 403 when configuring branch protection on a private free-tier repo. Impact: main cannot be protected (require PR, approvals, status checks), so the workflow guardrails documented in CONTRIBUTING are not enforced by the platform.
- **Possible Fix:** Publicize the repo when ready, then enable branch protection on `main`: require PRs, one approval, and passing status checks.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-009 — No Dependabot configuration
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** There is no `.github/dependabot.yml`. Composer, npm, and GitHub Actions dependencies are never auto-checked for updates. Impact: outdated or vulnerable dependencies go unnoticed until a human checks manually.
- **Possible Fix:** Add `.github/dependabot.yml` covering `composer`, `npm`, and `github-actions`, with a weekly schedule.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-010 — Extra merge methods enabled
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** The repository allows merge commits and rebase merges even though the workflow prefers squash merges. Impact: a contributor can merge a PR with a non-squash history, producing noisy or non-atomic history that contradicts CONTRIBUTING.
- **Possible Fix:** Disable `allow_merge_commit` and `allow_rebase_merge`; keep squash merge only.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-011 — `delete_branch_on_merge` is off
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-08-02
- **Implemented:** `—`
- **Problem:** Branches are not auto-deleted on merge. Impact: stale branches accumulate on the remote (as happened with PRs #8-#11), requiring manual cleanup and confusing `git branch -r` output.
- **Possible Fix:** Enable "Automatically delete head branches" in repository settings.
- **Actual Fix:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`
