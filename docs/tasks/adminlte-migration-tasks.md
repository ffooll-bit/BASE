---
title: adminlte-migration -- Task Backlog
status: draft
date: 2026-07-21
version: 0.2
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
**Verification**: `cd base && grep -q '"admin-lte"' package.json && grep -q '"@fortawesome/fontawesome-free"' package.json && test -d node_modules/admin-lte && test -d node_modules/bootstrap && test -d node_modules/@fortawesome/fontawesome-free && ! ls node_modules | grep -qi jquery`
**Status**: [ ] Not started

### TASK-002 | Create Asset Build Script and Populate Public Directory
**Description**: Add an npm `build` script to `package.json` that copies pre-compiled minified assets from `node_modules/` to `public/` under the correct directory structure. Use a single cross-platform copy command (`xcopy` on Windows, `cp` on Unix) or a minimal Node.js script. The build must place: `public/adminlte/css/adminlte.min.css`, `public/adminlte/js/adminlte.min.js`, `public/bootstrap/css/bootstrap.min.css`, `public/bootstrap/js/bootstrap.bundle.min.js`, `public/fontawesome/css/all.min.css`, and `public/fontawesome/webfonts/*`. Run the build and verify all target files exist.
**Dependencies**: TASK-001
**Priority**: High
**Traces to AC**: AC-08
**Verification**: `npm run build; if ($?) { $files='public/adminlte/css/adminlte.min.css','public/adminlte/js/adminlte.min.js','public/bootstrap/css/bootstrap.min.css','public/bootstrap/js/bootstrap.bundle.min.js','public/fontawesome/css/all.min.css'; $ok=$true; $files.ForEach({ if (-not (Test-Path $_)) { Write-Host "MISSING: $_"; $ok=$false } }); if ((Get-ChildItem public/fontawesome/webfonts/*).Count -eq 0) { Write-Host 'MISSING: webfonts/ empty'; $ok=$false }; if ($ok) { Write-Host 'All assets present' } }`
**Status**: [ ] Not started

## Group 2: Login Page Migration
**Goal**: Migrate the standalone login view from AdminLTE 3 to AdminLTE 4 markup with Bootstrap 5 forms, FA 6 icons, no jQuery or icheck

### TASK-003 | Migrate Login View to AdminLTE 4
**Description**: Rewrite `app/Views/login/login.php` to AdminLTE 4 login page markup. Use AdminLTE 4 CSS classes (`login-page`, `login-box`, `card`). Replace `data-dismiss` with `data-bs-dismiss` for alert dismissal. Use Bootstrap 5 native checkbox for "Remember Me" instead of icheck-bootstrap. Replace all CDN links with local `public/` paths for CSS. Use Font Awesome 6 `fas` icon classes. Remove all jQuery script tags, icheck CSS/JS references, and FA 5 CDN links. Preserve the form POST action (`/login`), CSRF token fields (`csrf_token()`, `csrf_field()`), and PHP flashdata variables (`$flashMessage`, `$flashType`).
**Dependencies**: TASK-002
**Priority**: High
**Traces to AC**: AC-01, AC-02
**Verification**: `cd base && grep -q 'adminlte.min.css' app/Views/login/login.php && grep -q 'data-bs-dismiss' app/Views/login/login.php && grep -q 'csrf' app/Views/login/login.php && ! grep -qi 'jquery\|icheck' app/Views/login/login.php`
**Status**: [ ] Not started

## Group 3: Layout Views Migration
**Goal**: Migrate the three layout partials (header, sidebar, footer) to AdminLTE 4 markup with Bootstrap 5 data attributes, local asset paths, no jQuery

### TASK-004 | Migrate Header View to AdminLTE 4
**Description**: Rewrite `app/Views/layout/header.php` to AdminLTE 4 navbar markup. Open HTML document with `<!DOCTYPE html>`, `<html>`, `<head>` containing local CSS links (`public/adminlte/css/adminlte.min.css`, `public/bootstrap/css/bootstrap.min.css`, `public/fontawesome/css/all.min.css`), `<body class="layout-fixed">`, and `<div class="wrapper">`. Render AdminLTE 4 navbar with pushmenu sidebar toggle button, branding text, and logout button. Replace all `data-*` attributes with Bootstrap 5 equivalents (`data-bs-toggle`, `data-bs-target`). Remove jQuery CDN script and FA 5 CDN link. Receive PHP `$username` for logout area display.
**Dependencies**: TASK-003
**Priority**: High
**Traces to AC**: AC-03
**Verification**: `cd base && grep -q 'adminlte.min.css' app/Views/layout/header.php && grep -q 'data-bs-toggle' app/Views/layout/header.php && grep -q 'layout-fixed' app/Views/layout/header.php && ! grep -qi 'jquery' app/Views/layout/header.php`
**Status**: [ ] Not started

### TASK-005 | Migrate Sidebar View to AdminLTE 4
**Description**: Rewrite `app/Views/layout/sidebar.php` to AdminLTE 4 sidebar markup. Render the user panel showing `$username`. Render navigation menu items from `$menuItems` array with active state highlighting based on `$currentRoute`. Use Bootstrap 5 collapse data attributes (`data-bs-toggle="collapse"`) for submenu treeview items instead of jQuery-based treeview. Ensure sidebar uses AdminLTE 4 CSS classes (`main-sidebar`, `sidebar`, `nav nav-pills nav-sidebar flex-column`). Open the `content-wrapper` div.
**Dependencies**: TASK-003
**Priority**: High
**Traces to AC**: AC-04
**Verification**: `cd base && grep -q 'nav-sidebar' app/Views/layout/sidebar.php && grep -q 'data-bs-toggle.*collapse' app/Views/layout/sidebar.php && grep -q 'user-panel' app/Views/layout/sidebar.php && ! grep -qi 'jquery' app/Views/layout/sidebar.php`
**Status**: [ ] Not started

### TASK-006 | Migrate Footer View to AdminLTE 4
**Description**: Rewrite `app/Views/layout/footer.php` to AdminLTE 4 footer markup. Close the `content-wrapper` div. Render the AdminLTE 4 footer bar with AdminLTE 4 classes. Close the `wrapper` div. Load JavaScript assets from local `public/` paths: Bootstrap 5 JS bundle (`public/bootstrap/js/bootstrap.bundle.min.js`) and AdminLTE 4 JS (`public/adminlte/js/adminlte.min.js`). Remove all CDN JS references, jQuery scripts, and any icheck JS initialization. Close `</body></html>`.
**Dependencies**: TASK-003
**Priority**: High
**Traces to AC**: AC-05
**Verification**: `cd base && grep -q 'adminlte.min.js' app/Views/layout/footer.php && grep -q 'bootstrap.bundle.min.js' app/Views/layout/footer.php && ! grep -qi 'jquery\|icheck' app/Views/layout/footer.php`
**Status**: [ ] Not started

## Group 4: Dashboard Migration
**Goal**: Migrate the dashboard content view to use AdminLTE 4 content-wrapper and Bootstrap 5 card components

### TASK-007 | Migrate Dashboard Index View to AdminLTE 4
**Description**: Rewrite `app/Views/dashboard/index.php` to render inside the `content-wrapper` div using AdminLTE 4 layout structure. Use Bootstrap 5 card markup (`card`, `card-header`, `card-body`) for the welcome card. Display `$username` in the welcome message. Update any content-header and content div classes to AdminLTE 4 equivalents. No functional changes -- purely visual migration.
**Dependencies**: TASK-006
**Priority**: High
**Traces to AC**: AC-06
**Verification**: `cd base && grep -q 'content-wrapper' app/Views/dashboard/index.php && grep -q 'card' app/Views/dashboard/index.php && grep -q '<?=.*\$username' app/Views/dashboard/index.php`
**Status**: [ ] Not started

## Group 5: Verification and Polish
**Goal**: Verify all 5 views for no jQuery, no console errors, correct asset loading, and visual match with AdminLTE 4 defaults

### TASK-008 | Verify All Pages -- No jQuery, No Console Errors, Visual Match
**Description**: Perform final verification across all 5 migrated views. Check that none of the view files contain `jQuery`, `$()`, `$.` or any jQuery plugin references. Load each page in a browser and inspect the console for JavaScript errors (especially missing jQuery, missing plugins, icheck init failures). Confirm all assets load from local `public/` paths with HTTP 200 status. Visually confirm each page matches AdminLTE 4's default styling (sidebar, navbar, card components). If issues are found, fix them directly in the view files.
**Dependencies**: TASK-007
**Priority**: High
**Traces to AC**: AC-09, AC-10, AC-11
**Verification**: `cd base && $views=@('app/Views/login/login.php','app/Views/layout/header.php','app/Views/layout/sidebar.php','app/Views/layout/footer.php','app/Views/dashboard/index.php'); $fail=$false; foreach ($v in $views) { if (Select-String -Path $v -Pattern 'jQuery|\$\(|jquery\.' -Quiet) { Write-Host "FAIL: jQuery ref in $v"; $fail=$true } }; if (-not $fail) { Write-Host 'PASS: No jQuery in any view' }`
**Status**: [ ] Not started

## Changelog
| Version | Timestamp | Editor | Changes |
|---------|-----------|--------|---------|
| 0.1 | 2026-07-21 10:21:57 | sdd-tasks (deepseek-v4-flash-free) | Initial draft |
| 0.2 | 2026-07-21 10:25:54 | organizer (deepseek-v4-flash-free) | Review: fix TASK-002 verification command to pure PowerShell for Windows compatibility (was mixed Unix/PowerShell) |