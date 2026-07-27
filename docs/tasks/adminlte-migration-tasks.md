---
title: adminlte-migration -- Task Backlog
status: approved
date: 2026-07-27
version: 0.3
spec-reference: docs/specifications/adminlte-migration-spec.md
plan-reference: docs/plans/adminlte-migration-plan.md
---

## Group 1: Foundation
**Goal**: Asset pipeline configuration -- remove old Composer dep, create npm package config, install deps, build script to copy assets to public/

### TASK-001 | Remove AdminLTE 3 from Composer and Create npm Package Config
**Description**: Edit `composer.json` to remove `almasaeed2010/adminlte ~3.2`. Create `package.json` with `admin-lte` and `@fortawesome/fontawesome-free` as dependencies. Run `npm install`. Verify `node_modules/` contains `admin-lte`, `bootstrap` (as transitive dep), and `@fortawesome/fontawesome-free`. No jQuery package shall be present in `node_modules/`.
**Dependencies**: None
**Priority**: High
**Traces to AC**: AC-07
**Verification**: `(Select-String -Path package.json -Pattern '"admin-lte"' -Quiet) -and (Select-String -Path package.json -Pattern '"@fortawesome/fontawesome-free"' -Quiet) -and (Test-Path node_modules/admin-lte) -and (Test-Path node_modules/bootstrap) -and (Test-Path node_modules/@fortawesome/fontawesome-free) -and -not (Get-ChildItem node_modules | Where-Object Name -like '*jquery*')`
**Status**: [X] Complete (2026-07-21)

### TASK-002 | Create Asset Build Script and Populate Public Directory
**Description**: Add an npm `build` script to `package.json` that copies pre-compiled minified assets from `node_modules/` to `public/` under the correct directory structure. Use a single cross-platform copy command (`xcopy` on Windows, `cp` on Unix) or a minimal Node.js script. The build must place: `public/adminlte/css/adminlte.min.css`, `public/adminlte/js/adminlte.min.js`, `public/bootstrap/css/bootstrap.min.css`, `public/bootstrap/js/bootstrap.bundle.min.js`, `public/fontawesome/css/all.min.css`, and `public/fontawesome/webfonts/*`. Run the build and verify all target files exist.
**Dependencies**: TASK-001
**Priority**: High
**Traces to AC**: AC-08
**Verification**: `npm run build; if ($?) { $files='public/adminlte/css/adminlte.min.css','public/adminlte/js/adminlte.min.js','public/bootstrap/css/bootstrap.min.css','public/bootstrap/js/bootstrap.bundle.min.js','public/fontawesome/css/all.min.css'; $ok=$true; $files.ForEach({ if (-not (Test-Path $_)) { Write-Host "MISSING: $_"; $ok=$false } }); if ((Get-ChildItem public/fontawesome/webfonts/*).Count -eq 0) { Write-Host 'MISSING: webfonts/ empty'; $ok=$false }; if ($ok) { Write-Host 'All assets present' } }`
**Status**: [X] Complete (2026-07-21)

## Group 2: Login Page Migration
**Goal**: Migrate the standalone login view from AdminLTE 3 to AdminLTE 4 markup with Bootstrap 5 forms, FA 6 icons, no jQuery or icheck

### TASK-003 | Migrate Login View to AdminLTE 4
**Description**: Rewrite `app/Views/login/login.php` to AdminLTE 4 login page markup. Use AdminLTE 4 CSS classes (`login-page`, `login-box`, `card`). Replace `data-dismiss` with `data-bs-dismiss` for alert dismissal. Use Bootstrap 5 native checkbox for "Remember Me" instead of icheck-bootstrap. Replace all CDN links with local `public/` paths for CSS. Use Font Awesome 6 `fas` icon classes. Remove all jQuery script tags, icheck CSS/JS references, and FA 5 CDN links. Preserve the form POST action (`/login`), CSRF token fields (`csrf_token()`, `csrf_field()`), and PHP flashdata variables (`$flashMessage`, `$flashType`).
**Dependencies**: TASK-002
**Priority**: High
**Traces to AC**: AC-01, AC-02
**Verification**: `(Select-String -Path app/Views/login/login.php -Pattern 'adminlte\.min\.css' -Quiet) -and (Select-String -Path app/Views/login/login.php -Pattern 'data-bs-dismiss' -Quiet) -and (Select-String -Path app/Views/login/login.php -Pattern 'csrf' -Quiet) -and -not (Select-String -Path app/Views/login/login.php -Pattern 'jquery|icheck' -Quiet)`
**Status**: [X] Complete (2026-07-21)

## Group 3: Layout Views Migration
**Goal**: Migrate the three layout partials (header, sidebar, footer) to AdminLTE 4 markup with Bootstrap 5 data attributes, local asset paths, no jQuery

### TASK-004 | Migrate Header View to AdminLTE 4
**Description**: Rewrite `app/Views/layout/header.php` to AdminLTE 4 navbar markup. Open HTML document with `<!DOCTYPE html>`, `<html>`, `<head>` containing local CSS links (`public/adminlte/css/adminlte.min.css`, `public/bootstrap/css/bootstrap.min.css`, `public/fontawesome/css/all.min.css`), `<body class="layout-fixed">`, and `<div class="wrapper">`. Render AdminLTE 4 navbar with pushmenu sidebar toggle button, branding text, and logout button. Replace all `data-*` attributes with Bootstrap 5 equivalents (`data-bs-toggle`, `data-bs-target`). Remove jQuery CDN script and FA 5 CDN link. Receive PHP `$username` for logout area display.
**Dependencies**: TASK-003
**Priority**: High
**Traces to AC**: AC-03
**Verification**: `(Select-String -Path app/Views/layout/header.php -Pattern 'adminlte\.min\.css' -Quiet) -and (Select-String -Path app/Views/layout/header.php -Pattern 'data-bs-toggle' -Quiet) -and (Select-String -Path app/Views/layout/header.php -Pattern 'layout-fixed' -Quiet) -and -not (Select-String -Path app/Views/layout/header.php -Pattern 'jquery' -Quiet)`
**Status**: [ ] Not started

### TASK-005 | Migrate Sidebar View to AdminLTE 4
**Description**: Rewrite `app/Views/layout/sidebar.php` to AdminLTE 4 sidebar markup. Render the user panel showing `$username`. Render navigation menu items from `$menuItems` array with active state highlighting based on `$currentRoute`. Use Bootstrap 5 collapse data attributes (`data-bs-toggle="collapse"`) for submenu treeview items instead of jQuery-based treeview. Ensure sidebar uses AdminLTE 4 CSS classes (`main-sidebar`, `sidebar`, `nav nav-pills nav-sidebar flex-column`). Open the `content-wrapper` div.
**Dependencies**: TASK-003
**Priority**: High
**Traces to AC**: AC-04
**Verification**: `(Select-String -Path app/Views/layout/sidebar.php -Pattern 'nav-sidebar' -Quiet) -and (Select-String -Path app/Views/layout/sidebar.php -Pattern 'data-bs-toggle.*collapse' -Quiet) -and (Select-String -Path app/Views/layout/sidebar.php -Pattern 'user-panel' -Quiet) -and -not (Select-String -Path app/Views/layout/sidebar.php -Pattern 'jquery' -Quiet)`
**Status**: [ ] Not started

### TASK-006 | Migrate Footer View to AdminLTE 4
**Description**: Rewrite `app/Views/layout/footer.php` to AdminLTE 4 footer markup. Close the `content-wrapper` div. Render the AdminLTE 4 footer bar with AdminLTE 4 classes. Close the `wrapper` div. Load JavaScript assets from local `public/` paths: Bootstrap 5 JS bundle (`public/bootstrap/js/bootstrap.bundle.min.js`) and AdminLTE 4 JS (`public/adminlte/js/adminlte.min.js`). Remove all CDN JS references, jQuery scripts, and any icheck JS initialization. Close `</body></html>`.
**Dependencies**: TASK-003
**Priority**: High
**Traces to AC**: AC-05
**Verification**: `(Select-String -Path app/Views/layout/footer.php -Pattern 'adminlte\.min\.js' -Quiet) -and (Select-String -Path app/Views/layout/footer.php -Pattern 'bootstrap\.bundle\.min\.js' -Quiet) -and -not (Select-String -Path app/Views/layout/footer.php -Pattern 'jquery|icheck' -Quiet)`
**Status**: [ ] Not started

## Group 4: Dashboard Migration
**Goal**: Migrate the dashboard content view to use AdminLTE 4 content-wrapper and Bootstrap 5 card components

### TASK-007 | Migrate Dashboard Index View to AdminLTE 4
**Description**: Rewrite `app/Views/dashboard/index.php` to render inside the `content-wrapper` div using AdminLTE 4 layout structure. Use Bootstrap 5 card markup (`card`, `card-header`, `card-body`) for the welcome card. Display `$username` in the welcome message. Update any content-header and content div classes to AdminLTE 4 equivalents. No functional changes -- purely visual migration.
**Dependencies**: TASK-006
**Priority**: High
**Traces to AC**: AC-06
**Verification**: `(Select-String -Path app/Views/dashboard/index.php -Pattern 'content-wrapper' -Quiet) -and (Select-String -Path app/Views/dashboard/index.php -Pattern '\bcard\b' -Quiet) -and (Select-String -Path app/Views/dashboard/index.php -Pattern '\$username' -Quiet)`
**Status**: [ ] Not started

## Group 5: Verification and Polish
**Goal**: Verify all 5 views for no jQuery, no console errors, correct asset loading, and visual match with AdminLTE 4 defaults

### TASK-008 | Verify All Pages -- No jQuery, No Console Errors, Visual Match
**Description**: Perform final verification across all 5 migrated views. Check that none of the view files contain `jQuery`, `$()`, `$.` or any jQuery plugin references. Load each page in a browser and inspect the console for JavaScript errors (especially missing jQuery, missing plugins, icheck init failures). Confirm all assets load from local `public/` paths with HTTP 200 status. Visually confirm each page matches AdminLTE 4's default styling (sidebar, navbar, card components). If issues are found, fix them directly in the view files.
**Dependencies**: TASK-007
**Priority**: High
**Traces to AC**: AC-09, AC-10, AC-11
**Verification**: `$views='app/Views/login/login.php','app/Views/layout/header.php','app/Views/layout/sidebar.php','app/Views/layout/footer.php','app/Views/dashboard/index.php'; $ok=$true; $views.ForEach({ if (Select-String -Path $_ -Pattern 'jQuery|\$\(|jquery\.' -Quiet) { Write-Host "FAIL: jQuery ref in $_"; $ok=$false } }); if ($ok) { Write-Host 'PASS: No jQuery in any view' }`
**Status**: [ ] Not started

## Changelog
| Version | Timestamp | Editor | Changes |
|---------|-----------|--------|---------|
| 0.1 | 2026-07-21 10:21:57 | sdd-tasks (deepseek-v4-flash-free) | Initial draft |
| 0.2 | 2026-07-21 10:25:54 | organizer (deepseek-v4-flash-free) | Review: fix TASK-002 verification command to pure PowerShell for Windows compatibility (was mixed Unix/PowerShell) |
| 0.3 | 2026-07-21 10:29:25 | organizer (deepseek-v4-flash-free) | Review again: fix all remaining verification commands (TASK-001, TASK-003, TASK-004, TASK-005, TASK-006, TASK-007, TASK-008) from Unix/bash to pure PowerShell for Windows consistency; remove erroneous 'cd base &&' prefixes |
| 0.3 | 2026-07-21 10:33:48 | Operator | **APPROVED** - backlog approved for Implement phase |
| 0.3 | 2026-07-21 10:52:32 | performer (deepseek-v4-flash-free) | TASK-001 completed: removed adminlte from composer, created package.json, npm install |
| 0.3 | 2026-07-27 09:04:37 | performer (deepseek-v4-flash-free) | TASK-002 completed: created build script, populated public/ with AdminLTE 4 assets |
| 0.3 | 2026-07-27 09:12:59 | performer (deepseek-v4-flash-free) | TASK-003 completed: migrated login view to AdminLTE 4 |