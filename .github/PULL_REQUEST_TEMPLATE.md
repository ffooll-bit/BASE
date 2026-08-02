## Summary

<!-- What does this PR do? Keep it short and clear. -->

## Related issues

<!-- Link the issue(s) this PR closes - use one "Fixes #N" line per issue. -->

Fixes #

## Checklist

- [ ] `php -l` passes on all modified PHP files
- [ ] `npm run build` succeeds and committed assets are up to date
- [ ] `php spark routes` shows correct new routes (if routes changed)
- [ ] `vendor/bin/php-cs-fixer fix` passes (no style violations)
- [ ] `vendor/bin/phpunit` is green (if tests exist)
- [ ] No debug code: `dd()`, `var_dump()`, `console.log()`, `print_r()`, `exit()`
- [ ] All user inputs validated server-side
- [ ] All POST forms include `csrf_field()`
- [ ] All HTML output uses `esc()`
- [ ] No unrelated files changed
- [ ] CHANGELOG updated if this is a user-facing change
- [ ] Behaviour verified in the browser if UI changed (attach a screenshot if useful)

## Screenshot (if applicable)

<!-- Paste screenshots here -->

## Notes for reviewers

<!-- Optional: anything the reviewer needs to know -->
