# Contributing to BASE

## Quick Start

```bash
composer install              # Backend dependencies
npm install && npm run build  # Frontend assets
vendor/bin/phpunit            # Run tests
```

Copy `env` to `.env`, configure `baseURL`, `encryption.key`, `neofeeder.*`.

**Dev server:** `php spark serve` (http://localhost:8080) or XAMPP pointing to `BASE/public/`.

---

## Development Workflow

Read `AGENTS.md` (Part A: Golden Path) for the full gate-based lifecycle.
This project follows: Plan → Execute → Docs & Commit → PR → Review → Merge & Cleanup.

Every phase has a required gate output. Skip a gate → rollback to previous phase.

---

## Branch Naming

```
feature/<kebab-case>   e.g. feature/data-mahasiswa
fix/<kebab-case>       e.g. fix/login-redirect-loop
chore/<kebab-case>     e.g. chore/update-phpunit
docs/<kebab-case>      e.g. docs/api-reference
```

Branch from `main`. One purpose, one PR, short-lived.

---

## Commit Messages

Conventional Commits format: English, lowercase after colon, no period, one change per commit.

```
feat: add mahasiswa data export endpoint
fix: handle empty NeoFeeder token response
chore: upgrade phpunit to 10.5
docs: update README setup instructions
refactor: extract NeoFeeder response parsing
```

Allowed types: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`, `perf`, `build`, `ci`, `revert`.

---

## Pull Request Checklist

Before opening a PR, confirm every item:

- [ ] `php -l` passes on all modified PHP files
- [ ] `npm run build` succeeds (if UI/assets changed)
- [ ] `php spark routes` matches expected routes (if routes changed)
- [ ] `vendor/bin/php-cs-fixer fix` passes
- [ ] `vendor/bin/phpunit` green (if tests exist)
- [ ] No debug code: `dd()`, `var_dump()`, `console.log()`, `print_r()`, `exit()`
- [ ] All user inputs validated server-side
- [ ] All HTML output uses `esc()`
- [ ] No unrelated files changed
- [ ] No magic numbers — extract named constants
- [ ] No code duplication — extract reusable logic
- [ ] Commit message follows Conventional Commits format
- [ ] Screenshots attached for UI changes

---

## Code Review

Focus on: Security (input validation, output escaping) → Correctness (edge cases, empty/error states) → Simplicity (dead code, unused variables) → Consistency (follows existing patterns).

Review comments: specific, actionable, respectful.

---

## Releases

This project follows [Semantic Versioning](https://semver.org/).

### Changelog Discipline

- Add entries to `[Unreleased]` in CHANGELOG.md throughout development.
- Before release: replace `## [Unreleased]` with `## [0.X.0] - YYYY-MM-DD`. Recreate fresh `[Unreleased]` with empty placeholders.
- Update `[unreleased]:` link to `HEAD`. Add `[0.X.0]:` link for the new version.

### Release Steps

Run after PR is merged and `main` is up-to-date:

```powershell
# 1. Ensure main is current
git switch main && git pull origin main

# 2. Update CHANGELOG.md — move [Unreleased] to versioned release
#    (edit manually, then:)
git add CHANGELOG.md && git commit -m "chore: release v0.X.0"

# 3. Tag and push
git tag v0.X.0 && git push origin v0.X.0

# 4. Create GitHub Release (extract only the version section)
$notes = [regex]::Match((Get-Content CHANGELOG.md -Raw), "(?s)## \[0\.X\.0\].*?(?=\n## \[|\z)").Value
$notes += "`r`n[0.X.0]: https://github.com/ffooll-bit/BASE/compare/v0.(X-1).0...v0.X.0"
[System.IO.File]::WriteAllText("$pwd\release_notes.md", $notes)
gh release create v0.X.0 --title "v0.X.0" --notes-file release_notes.md
Remove-Item release_notes.md

# 5. Update OPEN_ISSUES.md — mark released items
```

Before every release: `vendor/bin/phpunit`, `php -l`, `npm run build`.
