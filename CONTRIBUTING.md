# Contributing to BASE

## Quick Start

```bash
composer install              # Backend dependencies
npm install && npm run build  # Frontend assets
vendor/bin/phpunit            # Run tests
```

Copy `env` to `.env` and configure `baseURL`, `encryption.key`, and `neofeeder.*` settings before running.

**Development server options:**
- Built-in server: `php spark serve` (http://localhost:8080)
- XAMPP: point Apache document root to `BASE/public/`

---

## Development Workflow

For every task, follow this sequence in order. Do not skip phases.
Every phase has a GATE: output that must be produced before proceeding.
If a gate is skipped, stop and rollback to the previous phase.

### Phase A: Plan (Gate: present to user, wait for "Setuju")

1. Read `OPEN_ISSUES.md` — find task with highest priority and Open status
2. Read `.memory/memory.yaml` — cross-session context
3. Read `ARCHITECTURE.md`, `DESIGN.md`, `CHANGELOG.md` — understand system & history
4. Explore code that needs to change — understand current behavior
5. Determine minimal file set — touch only what the task requires
6. **Gate: Present plan to user (scope, files, approach, risk) — wait for "Setuju" or "Lanjutkan"**

### Phase B: Execute (Gate: verification passes)

1. `git switch main && git pull origin main`
2. Create branch from main: `feature/xxx` / `fix/xxx` / `chore/xxx` / `docs/xxx`
3. Implement — follow rules in `AGENTS.md` and Decision Framework
4. **Add tests** — required for libraries/services/controllers/filters, optional for view/docs
5. **Gate: `php -l` on every PHP file changed** — fix if it fails
6. **Gate: `vendor/bin/php-cs-fixer fix`** — auto-fix code style
7. **Gate: `npm run build` (if view/asset changed)** — fix if it fails
8. **Gate: `php spark routes` (if routes changed)** — fix if incorrect
9. **Gate: `vendor/bin/phpunit` (if tests exist)** — fix until passing
10. **Gate: Save to `.memory/memory.yaml`** — if any discovery was made

### Phase C: Docs & Commit (Gate: commit follows convention)

1. Update CHANGELOG.md — add entry under [Unreleased] for feat/fix/refactor/perf/security
2. Update OPEN_ISSUES.md — mark Done if task closes an issue
3. Update ARCHITECTURE.md — if routes/services/auth flow changed
4. Update DESIGN.md — if new UI pattern was added
5. **Gate: Pre-commit ritual. Six checks:**
   - [ ] PHP lint passes
   - [ ] npm build passes
   - [ ] phpunit passes
   - [ ] CHANGELOG updated (if needed)
   - [ ] OPEN_ISSUES updated (if needed)
   - [ ] memory.yaml updated (if new discoveries)
6. **Gate: Commit message — format `type: description`** — one commit per logical change

### Phase D: Pull Request (Gate: PR is open on GitHub)

1. Push branch to remote: `git push origin <branch-name>`
2. Open PR on GitHub — use `.github/PULL_REQUEST_TEMPLATE.md` as checklist
3. Wait for CI to pass — if it fails, amend commit (`git commit --amend`), force-push
4. Request manager review — wait for "Merge" or "Ada yang perlu diubah"

### Phase E: After Review (Gate: proceed based on response)

If manager says "Merge" or "Setuju":
  → Proceed to Phase F (Merge & Cleanup)

If manager says "Ada yang perlu diubah" or other feedback:
  → **DO NOT execute immediately. Open Change Request Protocol in AGENTS.md first.**

### Phase F: Merge & Cleanup (Gate: main updated, branches clean)

1. Merge PR via GitHub UI (rebase merge preferred)
2. Delete remote branch: `git push origin --delete <branch-name>`
3. `git switch main && git pull origin main`
4. Delete local branch: `git branch -d <branch-name>`
5. If release is needed → run Release Process

### Definition of Done

Task is **Done** when: code written, linted (`php -l`), verified (`npm run build` + `phpunit`), committed (Conventional Commit), PR sent with CI green, PR merged, branches cleaned, release created (if applicable).

---

## Branch Naming

```
feature/<kebab-case>    # New feature    e.g. feature/data-mahasiswa
fix/<kebab-case>        # Bug fix        e.g. fix/login-redirect-loop
chore/<kebab-case>      # Tooling/CI     e.g. chore/update-phpunit
docs/<kebab-case>       # Documentation  e.g. docs/api-reference
```

Always branch from `main`. Keep branches short-lived — one purpose, one PR.

---

## Commit Messages

Use Conventional Commits format. Rules: English, lowercase after colon, no period, one change per commit.

```
feat: add mahasiswa data export endpoint
fix: handle empty NeoFeeder token response
chore: upgrade phpunit to 10.5
docs: update README setup instructions
refactor: extract NeoFeeder response parsing logic
```

Allowed types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `build`, `ci`, `revert`.

---

## Pull Request Checklist

Before opening a PR, confirm every item:

- [ ] `php -l` passes on all modified PHP files
- [ ] `npm run build` succeeds (if UI/assets changed)
- [ ] `php spark routes` shows correct new routes (if routes changed)
- [ ] `vendor/bin/php-cs-fixer fix` passes (no style violations)
- [ ] `vendor/bin/phpunit` is green (if tests exist)
- [ ] No debug code: `dd()`, `var_dump()`, `console.log()`, `print_r()`, `exit()` are absent
- [ ] All user inputs are validated server-side
- [ ] All HTML output uses `esc()`
- [ ] No unrelated files were changed
- [ ] No magic numbers — extract named constants for hardcoded values
- [ ] No code duplication — extract reusable logic
- [ ] Commit message follows Conventional Commits format
- [ ] Screenshots attached for UI changes

---

## Code Review

Review your own code before requesting others. Focus on:

1. **Security** — Is input validated? Is output escaped?
2. **Correctness** — Does it handle edge cases? Empty state? Error state?
3. **Simplicity** — Could it be simpler? Is there dead code or unused variables?
4. **Consistency** — Does it follow existing patterns in the same codebase?

Review comments should be specific, actionable, and respectful.

---

## Releases

This project follows [Semantic Versioning](https://semver.org/) (MAJOR.MINOR.PATCH).

### Changelog Discipline

- Add new entries to `[Unreleased]` section in `CHANGELOG.md` throughout development (every feat/fix/refactor commit).
- Before release, replace `## [Unreleased]` with `## [0.X.0] - YYYY-MM-DD`. After moving, recreate a fresh `[Unreleased]` section with empty category placeholders (see the agent instructions embedded in CHANGELOG.md for template).
- Update the `[unreleased]:` link reference at the bottom of the file to point to `HEAD`.
- Add a new version link reference `[0.X.0]:` pointing to the previous version tag range.

### Release Steps

Run these after PR is merged and main is up-to-date:

```powershell
# 1. Ensure main is current
git switch main && git pull origin main

# 2. Update CHANGELOG.md — move [Unreleased] to versioned release
#    (edit manually, then:)
git add CHANGELOG.md && git commit -m "chore: release v0.X.0"

# 3. Tag and push
git tag v0.X.0 && git push origin v0.X.0

# 4. Create GitHub Release (extract only the version section from CHANGELOG.md)
$notes = [regex]::Match((Get-Content CHANGELOG.md -Raw), "(?s)## \[0\.X\.0\].*?(?=\n## \[|\z)").Value
$notes += "`r`n[0.X.0]: https://github.com/ffooll-bit/BASE/compare/v0.(X-1).0...v0.X.0"
[System.IO.File]::WriteAllText("$pwd\release_notes.md", $notes)
gh release create v0.X.0 --title "v0.X.0" --notes-file release_notes.md
Remove-Item release_notes.md

# 5. Update OPEN_ISSUES.md — mark released items
```

Before every release, run: `vendor/bin/phpunit`, `php -l` on changed files, `npm run build`.
