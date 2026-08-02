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

## Workflow

BASE follows the standard GitHub Flow:

1. Every change starts from a **GitHub Issue** (bug report or feature request).
2. Create a branch from `main` for the issue.
3. Implement the change.
4. Verify locally (see the checklist below).
5. Open a Pull Request that references the issue (`Fixes #N`).
6. Wait for CI and review. Fix CI failures with a fixup commit — never force-push.
7. Merge via GitHub UI (squash merge preferred), then delete the branch.

Never merge directly to `main` — always via Pull Request.

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

## Coding Conventions

### PHP

- PSR-12 (`@PSR12` in PHP CS Fixer). PHP 8.2+ required.
- Use typed properties, union/nullable types, match expressions, named arguments.
- No `declare(strict_types=1)` — may break existing code relying on type coercion.
- **Never use:** `dd()`, `var_dump()`, `print_r()`, `exit()`, `eval()`, `extract()`.
- Type hints required on all properties, parameters, and return types.
- PHPDoc on every public/protected method with `@param` and `@return`.
- Inline comments for WHY, not WHAT.

### CodeIgniter 4

- Access services via `service('name')` — never `new` for registered services.
- Access config via `config('NeoFeeder')->property`.
- Concatenate view partials (no template inheritance).
- All routes explicit in `app/Config/Routes.php`. No `$routes->autoRoute()`.
- Every POST form includes `<?= csrf_field() ?>` immediately after `<form>`.
- Use `$this->request->getPost('name')` — never `$_POST` / `$_GET`.

### Views

- **ALL dynamic output uses `esc()`**: `esc($username)`, `esc($value, 'url')`, `esc($value, 'attr')`.
- Use `<?= ?>` for output (never `<?php echo`).
- Alternate control structures (`if:` / `endif;`).
- Asset URLs via `base_url('bootstrap/css/bootstrap.min.css')`.

### JavaScript & CSS

- **jQuery is banned.** Vanilla JS only.
- Bootstrap 5 components via `data-bs-*` attributes and the native API.
- `console.log()` must never reach a commit.
- Build layouts with Bootstrap 5 utility classes before writing custom CSS.
- Font Awesome 6 icons only. Sidebar icons include `nav-icon` class.

### Anti-patterns

| # | Don't | Do |
|---|-------|----|
| 1 | `dd()`, `var_dump()`, `print_r()`, `exit()` | Remove before commit |
| 2 | `new ServiceClass()` for registered services | `service('name')` |
| 3 | Hardcoded URLs like `/public/index.php/dashboard` | `base_url('dashboard')` |
| 4 | `array()` syntax | `[]` short array |
| 5 | Bootstrap 4 utilities (`ml-*`, `data-toggle`) | BS5 equivalents (`ms-*`, `data-bs-toggle`) |
| 6 | AdminLTE 3 classes (`wrapper`, `main-sidebar`) | AL4 classes (`app-wrapper`, `app-sidebar`) |
| 7 | `onclick` HTML attributes | `addEventListener('click', handler)` or `data-bs-*` |
| 8 | PHP closing tag `?>` at end of pure PHP files | Omit |

### Decision Framework

When a choice has non-trivial trade-offs, resolve by priority:

1. **Security** — protects user data and prevents abuse
2. **Correctness** — works for all cases, including edge cases
3. **Simplicity** — fewer components
4. **Performance** — fast enough (not "fastest possible")
5. **Aesthetics** — clean and consistent

When in doubt: CI4 built-in features over custom code, Bootstrap utilities over custom CSS, vanilla JS over libraries.

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
- [ ] All POST forms include `csrf_field()`
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

# 4. Create GitHub Release
#    Copy .github/RELEASE_NOTES_TEMPLATE.md, fill in the release-specific
#    sections (Summary, Added, Changed, Fixed, ...), and remove the ones
#    that don't apply. Save as release_notes.md, then:
gh release create v0.X.0 --title "v0.X.0" --notes-file release_notes.md
Remove-Item release_notes.md
```

Before every release: `vendor/bin/phpunit`, `php -l`, `npm run build`.
