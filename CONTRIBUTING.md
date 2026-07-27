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

### Phase A: Understand

1. Read `AGENTS.md` if this is your first session — it defines your identity, rules, and decision framework.
2. Read the task description or `OPEN_ISSUES.md` entry carefully. Identify what needs to change and why.
3. If the task is ambiguous, ask clarifying questions **before** writing code. Spending 30 minutes planning is cheaper than 3 hours on the wrong solution.
4. Read `ARCHITECTURE.md` if it exists — it explains how the system works.
5. Read `DESIGN.md` if it exists and the task involves UI changes.
6. Read `CODING_STANDARDS.md` if it exists.

### Phase B: Plan

1. Make sure your local `main` is up to date. Stash or commit any pending changes first, then:
   ```bash
   git switch main && git pull origin main
   ```
2. Create a branch from `main` following the naming convention below.
3. Examine the existing code that needs to change. Understand the current behavior.
4. Decide the minimal set of files to modify. Touch nothing outside the task scope.
5. For complex tasks (3+ files or cross-cutting changes), write a short plan and get confirmation before coding.

### Phase C: Implement

1. Write or modify code following project conventions and the Decision Framework in `AGENTS.md`.
2. Keep changes minimal — only what the task requires (YAGNI).
3. Self-review as you go: check for security, edge cases, and debug leftovers.
4. **Add tests:**
   - **Always** add tests when creating or modifying libraries, services, controllers, or filters
   - **Required** when fixing a bug — write a test that reproduces the bug first
   - **Optional** for view-only changes or documentation

### Phase D: Verify

Run these in order. Stop and fix immediately if any step fails:

```bash
# Check PHP syntax on every file you touched (example):
php -l app/Controllers/Login.php
php -l app/Libraries/Auth.php
# Repeat for every PHP file you created or modified

npm run build           # Only if you touched any view or asset file
php spark routes        # Only if routes changed
vendor/bin/phpunit      # Only if tests exist
```

### Phase E: Commit

1. Stage only files related to this task. No unrelated changes.
2. Write a commit message following the format below.
3. Commit. One commit per logical change.

**What counts as one commit:**
- A new feature spanning controller + service + view = one commit
- A bug fix and its corresponding test = one commit
- Refactoring unrelated code while fixing a bug = two separate commits

### Phase F: Pull Request

1. Push your branch.
2. Open a PR. If `.github/PULL_REQUEST_TEMPLATE.md` exists, use it as a checklist.
3. Wait for CI to pass. If it fails, fix and amend the commit — do not add fixup commits.

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
- [ ] `vendor/bin/phpunit` is green (if tests exist)
- [ ] No debug code: `dd()`, `var_dump()`, `console.log()`, `print_r()`, `exit()` are absent
- [ ] All user inputs are validated server-side
- [ ] All HTML output uses `esc()`
- [ ] No unrelated files were changed
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

- This project follows [Semantic Versioning](https://semver.org/) (MAJOR.MINOR.PATCH).
- If `CHANGELOG.md` exists, update it throughout development: add new entries to the `[Unreleased]` section as changes are made. Before release, move them under a new version header.
- A release is tagged from `main` after the PR is merged.
- Release command: `git tag v<version> && git push origin v<version>`
