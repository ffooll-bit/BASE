## Deskripsi Perubahan

<!-- Jelaskan apa yang diubah dan kenapa -->

## Jenis Perubahan

- [ ] feat — Fitur baru
- [ ] fix — Perbaikan bug
- [ ] refactor — Refactoring (tidak mengubah fungsionalitas)
- [ ] chore — Tooling, dependency, CI
- [ ] docs — Dokumentasi
- [ ] test — Test

## Checklist (Self-Review)

- [ ] `php -l` passes on all modified PHP files
- [ ] `npm run build` succeeds (if UI/assets changed)
- [ ] `php spark routes` shows correct new routes (if routes changed)
- [ ] `vendor/bin/phpunit` is green (if tests exist)
- [ ] No debug code: `dd()`, `var_dump()`, `console.log()`, `print_r()`, `exit()`
- [ ] All user inputs are validated server-side
- [ ] All POST forms include `csrf_field()`
- [ ] All HTML output uses `esc()`
- [ ] No unrelated files were changed
- [ ] No magic numbers — extract named constants
- [ ] No code duplication — extract reusable logic
- [ ] `vendor/bin/php-cs-fixer fix` passes (no style violations)
- [ ] Commit message follows Conventional Commits format
- [ ] Screenshots attached for UI changes

## Screenshot (jika ada)

<!-- Tempel screenshot di sini -->

## Catatan Tambahan

<!-- Optional: hal-hal yang perlu reviewer ketahui -->
