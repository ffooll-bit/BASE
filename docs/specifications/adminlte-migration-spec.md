---
title: AdminLTE 3 to AdminLTE 4 Migration
status: approved
date: 2026-07-11
version: 0.2
---

## Introduction

BASE is a CodeIgniter 4 web application using AdminLTE 3.2 (Composer), Bootstrap 4.6.2 (CDN), jQuery 3.6 (CDN), Font Awesome 5.15.4 (CDN), and Icheck Bootstrap 3.0.1 (CDN, login page only). This specification defines the migration of the UI layer from AdminLTE 3 to AdminLTE 4, which carries Bootstrap 5.3 and Font Awesome 6 as dependencies and uses vanilla JavaScript (no jQuery).

The migration is strictly a UI template change -- no backend logic, controllers, models, routes, or authentication code will be modified. Only 5 views are affected: login page, layout header, layout sidebar, layout footer, and dashboard index page.

### Problem Statement

AdminLTE 3 is in maintenance mode. AdminLTE 4 provides:
- Bootstrap 5.3 (modern, actively maintained)
- Vanilla JS (no jQuery dependency)
- Font Awesome 6 (latest icon set)
- Improved component structure and accessibility

Continuing on AdminLTE 3 means accumulating tech debt and missing security and compatibility updates from the Bootstrap/AdminLTE ecosystem.

### Scope

This specification covers:
- Migration of 5 view files from AdminLTE 3 to AdminLTE 4 markup
- Replacement of Composer dependency with npm-based AdminLTE 4
- Asset bundling/copying from node_modules to public/
- Removal of jQuery from all views
- Removal of icheck-bootstrap dependency
- Upgrade of Font Awesome from 5.15.4 to 6.x

## Functional Requirements

### FR-01: Login Page Migration
**Priority**: High
**Description**: Migrate `app/Views/login/login.php` from AdminLTE 3 login page markup to AdminLTE 4 login page markup. The page is a standalone full HTML document (not loaded inside the layout wrapper). It must use AdminLTE 4 CSS classes, Bootstrap 5 form markup, and Font Awesome 6 icons. The login form must POST to `/login` with CSRF token. Flash error/message alerts must use Bootstrap 5 alert dismissal (`data-bs-dismiss` instead of `data-dismiss`). No jQuery or icheck-bootstrap shall be included.
**Traces to**: AC-01, AC-02

### FR-02: Layout Header Migration
**Priority**: High
**Description**: Migrate `app/Views/layout/header.php` from AdminLTE 3 navbar markup to AdminLTE 4 navbar markup. Must include the pushmenu sidebar toggle button, the application branding, and a logout button. Must use Bootstrap 5 classes and data attributes. The header opens the HTML document (DOCTYPE, html, head with CSS links, body tag, wrapper div).
**Traces to**: AC-03

### FR-03: Layout Sidebar Migration
**Priority**: High
**Description**: Migrate `app/Views/layout/sidebar.php` from AdminLTE 3 sidebar markup to AdminLTE 4 sidebar markup. Must include the user panel (showing authenticated username), navigation menu items, and Bootstrap 5 data attributes for collapse/accordion behavior. The sidebar opens the content-wrapper div.
**Traces to**: AC-04

### FR-04: Layout Footer Migration
**Priority**: High
**Description**: Migrate `app/Views/layout/footer.php` from AdminLTE 3 footer markup to AdminLTE 4 footer markup. Must close the content-wrapper div, render the AdminLTE 4 footer, close the wrapper, and load JavaScript assets (Bootstrap 5 bundle, AdminLTE 4 JS) from local npm-built paths (public/). Must close the HTML document.
**Traces to**: AC-05

### FR-05: Dashboard Index Migration
**Priority**: High
**Description**: Migrate `app/Views/dashboard/index.php` from AdminLTE 3 content markup to AdminLTE 4 content markup. Must render inside the content-wrapper using Bootstrap 5 card components. No functional changes -- purely visual.
**Traces to**: AC-06

### FR-06: Composer Dependency Replacement
**Priority**: High
**Description**: Remove the Composer dependency `almasaeed2010/adminlte ~3.2` from `composer.json`. Create or update `package.json` with npm dependencies: AdminLTE 4 (provides Bootstrap 5.3), and Font Awesome 6. The npm install must succeed and populate `node_modules/` with the required packages.
**Traces to**: AC-07

### FR-07: NPM Asset Setup
**Priority**: High
**Description**: Set up `package.json` with the following npm dependencies:
- `admin-lte` (AdminLTE 4, includes Bootstrap 5.3 JS/CSS)
- `@fortawesome/fontawesome-free` (Font Awesome 6)

No other runtime dependencies shall be added. jQuery must not appear in `package.json` dependencies.
**Traces to**: AC-07

### FR-08: Asset Bundling to Public Directory
**Priority**: High
**Description**: Copy or bundle required CSS and JS assets from `node_modules/` into the `public/` directory so they are served by the web server. The following assets must be available:
- AdminLTE CSS (`public/adminlte/css/adminlte.min.css`)
- AdminLTE JS (`public/adminlte/js/adminlte.min.js`)
- Bootstrap 5 CSS (`public/bootstrap/css/bootstrap.min.css`)
- Bootstrap 5 JS bundle (`public/bootstrap/js/bootstrap.bundle.min.js`)
- Font Awesome 6 CSS (`public/fontawesome/css/all.min.css`)
- Font Awesome 6 webfonts (`public/fontawesome/webfonts/`)

A build script (npm script or shell script) may be used for this step.
**Traces to**: AC-08

## Non-Functional Requirements

### NFR-01: Remove All jQuery
**Priority**: High
**Description**: No jQuery shall be loaded or referenced in any of the 5 views. AdminLTE 4 uses vanilla JavaScript natively. No jQuery plugins (Bootstrap JS plugins, icheck, etc.) shall be included.
**Traces to**: AC-01, AC-03, AC-04, AC-09, AC-10

### NFR-02: No Backend Logic Changes
**Priority**: High
**Description**: Controllers, models, routes, authentication logic, sessions, flashdata handling, and all other backend code shall remain untouched. The migration is limited to view files and asset management (composer.json, package.json, build scripts).
**Traces to**: AC-02, AC-06

### NFR-03: Visual Consistency
**Priority**: High
**Description**: All migrated views shall follow AdminLTE 4's default markup, CSS class names, and component structure. The appearance shall match AdminLTE 4's default look, not a retro-fit of the AdminLTE 3 appearance. Custom CSS beyond what AdminLTE 4 provides by default is out of scope.
**Traces to**: AC-11

### NFR-04: No New Features
**Priority**: High
**Description**: No new functionality shall be introduced. The 5 views shall preserve all existing behavior (login form submit, flash messages, navigation, logout). Only the markup and asset references change.
**Traces to**: AC-02, AC-03, AC-04, AC-05, AC-06

### NFR-05: Remove Icheck Bootstrap
**Priority**: Medium
**Description**: The icheck-bootstrap dependency (used on the login page for "Remember Me" checkbox styling) shall be removed. Bootstrap 5 native checkbox styling replaces it.
**Traces to**: AC-01

### NFR-06: Font Awesome Upgrade
**Priority**: Medium
**Description**: Font Awesome shall be upgraded from version 5.15.4 to version 6.x (as provided by `@fortawesome/fontawesome-free`), aligned with AdminLTE 4's dependency. All icon class references in the 5 views shall use the v6 syntax (`fas`, `far`, `fab` prefix conventions remain compatible).
**Traces to**: AC-01, AC-03, AC-04

## Acceptance Criteria

### AC-01: Login Renders with AdminLTE 4 and No jQuery/Icheck (traces to FR-01, NFR-01, NFR-05)
**Given** a user who is not authenticated
**When** they navigate to `/login`
**Then** the page shall:
- Render with AdminLTE 4 CSS classes
- Use Bootstrap 5 form markup
- Use Font Awesome 6 icons
- Contain NO jQuery script tags
- Contain NO icheck-bootstrap CSS or JS
- Use `data-bs-dismiss` for alert dismissal (not `data-dismiss`)

### AC-02: Login Form Posts and Shows Flash Alerts (traces to FR-01, NFR-02, NFR-04)
**Given** a user on the login page
**When** they submit the login form
**Then** the form shall POST to `/login` with CSRF token
**And** flash error/message alerts shall display using Bootstrap 5 alert classes
**And** alert dismiss buttons shall use `data-bs-dismiss="alert"`

### AC-03: Header Renders AdminLTE 4 Navbar (traces to FR-02, NFR-01, NFR-04, NFR-06)
**Given** an authenticated user viewing any layout page
**When** the page loads
**Then** the header shall:
- Render an AdminLTE 4 navbar with pushmenu sidebar toggle
- Include a logout button
- Use Bootstrap 5 classes and data attributes
- Contain NO jQuery
- Use Font Awesome 6 icons for any icon elements

### AC-04: Sidebar Renders AdminLTE 4 Sidebar (traces to FR-03, NFR-01, NFR-04, NFR-06)
**Given** an authenticated user viewing any layout page
**When** the page loads
**Then** the sidebar shall:
- Render an AdminLTE 4 sidebar with user panel showing the authenticated username
- Include navigation menu items
- Use Bootstrap 5 data attributes for collapse behavior
- Contain NO jQuery

### AC-05: Footer Renders and Loads Local Assets (traces to FR-04, FR-08, NFR-04)
**Given** an authenticated user viewing any layout page
**When** the page loads
**Then** the footer shall:
- Render an AdminLTE 4 footer
- Load JavaScript from local `public/` paths (not CDN)
- Load Bootstrap 5 JS bundle (`bootstrap.bundle.min.js`)
- Load AdminLTE 4 JS (`adminlte.min.js`)

### AC-06: Dashboard Renders in Content-Wrapper (traces to FR-05, NFR-02, NFR-04)
**Given** an authenticated user
**When** they navigate to `/dashboard`
**Then** the page shall:
- Render inside the content-wrapper div
- Use Bootstrap 5 card markup for the welcome card
- Display the authenticated username (via PHP session data, unchanged from backend)

### AC-07: NPM Install Produces Required Dependencies (traces to FR-06, FR-07)
**Given** a developer
**When** they run `npm install`
**Then** `node_modules/` shall contain:
- `admin-lte` (AdminLTE 4)
- `bootstrap` (5.3.x, as a dependency of admin-lte)
- `@fortawesome/fontawesome-free` (6.x)
- No jQuery package

### AC-08: Build Script Copies Assets to Public (traces to FR-08)
**Given** a developer
**When** they run the build/copy script
**Then** the following files shall exist in `public/`:
- `public/adminlte/css/adminlte.min.css`
- `public/adminlte/js/adminlte.min.js`
- `public/bootstrap/css/bootstrap.min.css`
- `public/bootstrap/js/bootstrap.bundle.min.js`
- `public/fontawesome/css/all.min.css`
- `public/fontawesome/webfonts/` (directory with webfont files)

### AC-09: No jQuery in Any View (traces to NFR-01)
**Given** each of the 5 views (`login.php`, `header.php`, `sidebar.php`, `footer.php`, `dashboard/index.php`)
**When** the view is rendered
**Then** the HTML output shall contain NO `<script` tag loading jQuery
**And** no JavaScript code referencing `$()` or `jQuery` from the AdminLTE template

### AC-10: No Console JS Errors (traces to NFR-01)
**Given** an authenticated user viewing any layout page
**When** the page loads and the browser console is inspected
**Then** there shall be no JavaScript errors related to:
- Missing jQuery
- Missing jQuery plugins that AdminLTE 3 required
- Missing icheck initialization

### AC-11: Visual Match with AdminLTE 4 Default (traces to NFR-03)
**Given** an authenticated user
**When** they view any of the 5 pages
**Then** the visual appearance shall match AdminLTE 4's default styling
**And** not resemble AdminLTE 3's look (different sidebar, navbar, card component styles)

## Out of Scope

- Any views or pages beyond the 5 listed (login, header, sidebar, footer, dashboard/index)
- New features or functionality changes
- Backend, controller, model, or route changes
- Custom CSS beyond what AdminLTE 4 provides by default
- Unit tests or E2E tests (manual visual verification only)
- CI/CD pipeline changes
- Migration of any JavaScript plugin logic present in original AdminLTE 3 views beyond what AdminLTE 4 provides natively
- Performance optimization beyond what ships with AdminLTE 4 defaults
- Accessibility audit or remediation

## Changelog

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 0.1 | 2026-07-11 | sdd-specify (big-pickle) | Initial draft |
| 0.2 | 2026-07-11 | Operator | Fix line wrapping in descriptions — no mid-sentence newlines |
| 0.2 | 2026-07-21 09:55:27 | Operator | **APPROVED** - specification approved for Plan phase |
| 0.2 | 2026-07-11 | Operator | Fix line wrapping in descriptions — no mid-sentence newlines |