# DESIGN.md — UI/UX Design System

This project uses **AdminLTE 4** (built on **Bootstrap 5.3**) with **Font Awesome 6** icons.
This document is the single source of truth for UI markup — every view must match these patterns.

---

## 1. Layout Anatomy

### 1.1 Page assembly

The page is assembled in the controller from 4 partials concatenated in order:

```
view('layout/header')
+ view('layout/sidebar')
+ view('{page}')          // page-specific content
+ view('layout/footer')
```

Pass data individually to each partial — `$title` and `$username` to header, page-specific data to the content view.

### 1.2 Body & wrapper

```html
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
```

| Class | Purpose |
|-------|---------|
| `layout-fixed` | Sidebar scrolls independently; only main content scrolls |
| `sidebar-expand-lg` | Inline at ≥992px, off-canvas below |
| `bg-body-tertiary` | Bootstrap 5 page background |

### 1.3 Header (`layout/header.php`)

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'BASE') ?></title>
    <link rel="stylesheet" href="<?= base_url('fontawesome/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('adminlte/css/adminlte.min.css') ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <nav class="app-header navbar navbar-expand bg-body">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ms-auto d-flex align-items-center">
            <li class="nav-item">
                <span class="nav-link text-nowrap d-none d-md-inline">
                    Signed in as: <?= esc($username) ?>
                </span>
            </li>
            <li class="nav-item">
                <form action="<?= base_url('logout') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="nav-link btn btn-link border-0 py-2">
                        <i class="fas fa-right-from-bracket"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </nav>
```

### 1.4 Sidebar (`layout/sidebar.php`)

```html
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= base_url('dashboard') ?>" class="brand-link">
                <span class="brand-text fw-light">BASE</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                    <!-- Navigation items (see Section 2) -->
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
```

### 1.5 Footer (`layout/footer.php`)

```html
    </main>

    <footer class="app-footer">
        <strong>Copyright &copy; <?= date('Y') ?> BASE.</strong>
        All rights reserved.
    </footer>

</div>

<script src="<?= base_url('bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= base_url('adminlte/js/adminlte.min.js') ?>"></script>
</body>
</html>
```

### 1.6 Page-specific view structure

```html
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($title) ?></h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active"><?= esc($title) ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <!-- Page content here -->
    </div>
</div>
```

---

## 2. Navigation

### 2.1 Basic menu item

```html
<li class="nav-item">
    <a href="<?= base_url('dashboard') ?>" class="nav-link">
        <i class="nav-icon fas fa-gauge-high"></i>
        <p>Dashboard</p>
    </a>
</li>
```

Always use `nav-icon` class on sidebar icons.

### 2.2 Active state

```html
<a href="<?= base_url('current-page') ?>" class="nav-link active">
```

Add `active` to the `nav-link` that matches the current route.

### 2.3 Submenu / Treeview

```html
<li class="nav-item">
    <a href="#" class="nav-link">
        <i class="nav-icon fas fa-folder"></i>
        <p>
            Parent Menu
            <i class="nav-arrow fas fa-angle-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="<?= base_url('child-page') ?>" class="nav-link">
                <i class="nav-icon far fa-circle"></i>
                <p>Child Item</p>
            </a>
        </li>
    </ul>
</li>
```

Parent `<ul>` needs `data-lte-toggle="treeview"`. Child icons use `far fa-circle`.

### 2.4 Menu section header

```html
<li class="nav-header">MAIN NAVIGATION</li>
```

### 2.5 Menu open state

Add `menu-open` to `li.nav-item` when a child page is active and treeview should be expanded:

```html
<li class="nav-item menu-open">
    <a href="#" class="nav-link active">
        <i class="nav-icon fas fa-folder-open"></i>
        <p>Parent Menu <i class="nav-arrow fas fa-angle-right"></i></p>
    </a>
    <ul class="nav nav-treeview">...</ul>
</li>
```

---

## 3. Page Header

### 3.1 Standard header

```html
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($title) ?></h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active"><?= esc($title) ?></li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
```

### 3.2 Header with action button

```html
<div class="col-sm-6">
    <div class="float-sm-end">
        <a href="<?= base_url('resource/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New
        </a>
    </div>
</div>
```

### 3.3 Header with breadcrumb + action side-by-side

```html
<div class="col-sm-6 d-flex align-items-center">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="#">Home</a></li>
            <li class="breadcrumb-item active">Current</li>
        </ol>
    </nav>
</div>
<div class="col-sm-6">
    <a href="#" class="btn btn-primary float-sm-end">
        <i class="fas fa-plus"></i> Add
    </a>
</div>
```

---

## 4. Card Patterns (Project-Specific)

### 4.1 AdminLTE small box (dashboard stat cards)

```html
<div class="small-box bg-info">
    <div class="inner">
        <h3><?= esc($count) ?></h3>
        <p>Label</p>
    </div>
    <div class="small-box-icon">
        <i class="fas fa-users"></i>
    </div>
    <a href="#" class="small-box-footer">
        More info <i class="fas fa-arrow-circle-right"></i>
    </a>
</div>
```

Backgrounds: `bg-info`, `bg-success`, `bg-warning`, `bg-danger`, `bg-primary`.

### 4.2 Hero card (profile/detail header)

```html
<div class="card card-outline card-primary mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="display-6 mb-1"><?= esc($entity['name']) ?></h2>
                <div class="d-flex gap-4 small text-muted">
                    <div><span>Field A:</span> <?= esc($entity['field_a'] ?? '-') ?></div>
                    <div><span>Field B:</span> <?= esc($entity['field_b'] ?? '-') ?></div>
                </div>
            </div>
            <span class="badge bg-success fs-6">Status: <?= esc($entity['status'] ?? '-') ?></span>
        </div>
        <hr class="my-3">
        <div class="d-flex gap-4 small">
            <div><span class="text-muted">Sub Info 1:</span> <?= esc($entity['info_1'] ?? '-') ?></div>
            <div><span class="text-muted">Sub Info 2:</span> <?= esc($entity['info_2'] ?? '-') ?></div>
        </div>
    </div>
</div>
```

Never combine `display-6` with `card-title` — `card-title` overrides display-* in Bootstrap 5.3.

### 4.3 Detail info card (equal-height pair)

```html
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card card-info h-100">
            <div class="card-header">
                <h3 class="card-title">Group A</h3>
            </div>
            <div class="card-body d-flex flex-column">
                <div class="fs-6 mb-2">
                    <i class="fas fa-phone text-muted me-2 fa-fw"></i> <?= esc($item['phone'] ?? '-') ?>
                </div>
                <div class="fs-6 mb-2">
                    <i class="fas fa-envelope text-muted me-2 fa-fw"></i>
                    <a href="mailto:<?= esc($item['email'], 'url') ?>"><?= esc($item['email']) ?></a>
                </div>
                <div class="fs-6 mt-auto">
                    <i class="fas fa-globe text-muted me-2 fa-fw"></i>
                    <a href="<?= esc($website, 'url') ?>" target="_blank"><?= esc($item['website']) ?></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card card-info h-100">
            <div class="card-header">
                <h3 class="card-title">Group B</h3>
            </div>
            <div class="card-body d-flex flex-column">
                <p class="fs-6 mb-1"><?= esc($item['address'] ?? '-') ?></p>
                <p class="fs-6 mb-0 mt-auto">Kode Pos: <?= esc($item['postal_code'] ?? '-') ?></p>
            </div>
        </div>
    </div>
</div>
```

| Technique | Classes | Effect |
|-----------|---------|--------|
| Equal height | `h-100` on card | Both cards fill column height |
| Content alignment | `d-flex flex-column` on card-body | Flex column for vertical positioning |
| Push to bottom | `mt-auto` on last item | Last element stays at card bottom |
| Icon width | `fa-fw` on icon | Fixed 1.25em width, icons align |

---

## 5. Flash Messages

### 5.1 Flashdata conventions

| Key | Alert class | Purpose |
|-----|-------------|---------|
| `error` | `alert-danger` | Validation errors, login failure, operation failed |
| `message` | `alert-info` | Info, session expired, notifications |
| `success` | `alert-success` | Operation succeeded |

Place alerts at the **top of `app-content`**, before the first card:

```html
<div class="app-content">
    <div class="container-fluid">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
```

---

## 6. Icons

### 6.1 Purpose-to-icon reference

| Purpose | Icon class |
|---------|-----------|
| Dashboard | `fas fa-gauge-high` |
| Add / Create | `fas fa-plus` |
| Edit | `fas fa-edit` |
| Delete | `fas fa-trash` |
| View / Detail | `fas fa-eye` |
| Save | `fas fa-save` |
| Search | `fas fa-search` |
| Back | `fas fa-arrow-left` |
| Forward | `fas fa-arrow-right` |
| Export | `fas fa-file-export` |
| Print | `fas fa-print` |
| Refresh | `fas fa-sync` |
| User | `fas fa-user` |
| Users | `fas fa-users` |
| Settings | `fas fa-cog` |
| Logout | `fas fa-right-from-bracket` |
| Menu | `fas fa-bars` |
| Close / Cancel | `fas fa-times` |
| Warning | `fas fa-exclamation-triangle` |
| Success | `fas fa-check-circle` |
| Error | `fas fa-times-circle` |
| Mail | `fas fa-envelope` |
| Lock | `fas fa-lock` |
| Calendar | `fas fa-calendar` |
| Home | `fas fa-home` |
| Folder | `fas fa-folder` |
| Folder Open | `fas fa-folder-open` |
| File | `fas fa-file` |
| Chart | `fas fa-chart-bar` |
| Notification | `fas fa-bell` |

### 6.2 Icon placement

| Context | Class | Example |
|---------|-------|---------|
| Sidebar | `nav-icon` | `<i class="nav-icon fas fa-..."></i>` |
| Button icon+text | none | `<i class="fas fa-..."></i> Label` |
| Button icon-only | `title` attr | `<i class="fas fa-..." title="Edit"></i>` |
| Input group | inside `input-group-text` | `<div class="input-group-text"><span class="fas fa-..."></span></div>` |

---

## 7. Anti-Patterns

| # | Mistake | Wrong | Correct |
|---|---------|-------|---------|
| 1 | AdminLTE 3 classes | `<div class="wrapper">` | `<div class="app-wrapper">` |
| 2 | Missing `esc()` | `<?= $username ?>` | `<?= esc($username) ?>` |
| 3 | Using jQuery | `$(document).ready()` | Vanilla JS or `data-*` attributes |
| 4 | `data-*` instead of `data-bs-*` | `data-toggle="dropdown"` | `data-bs-toggle="dropdown"` |
| 5 | AdminLTE 3 sidebar classes | `nav-sidebar` | `sidebar-menu` |
| 6 | Bootstrap 4 utilities | `ml-auto`, `float-right` | `ms-auto`, `float-end` |
| 7 | `form-control` on select | `<select class="form-control">` | `<select class="form-select">` |
| 8 | Missing `form-label` | `<label>Field</label>` | `<label class="form-label">Field</label>` |
| 9 | Missing `csrf_field()` | `<form>` without CSRF | `<?= csrf_field() ?>` after form open |
| 10 | Inline styles for layout | `style="width:250px"` | AdminLTE SCSS variables |
| 11 | Debug output | `<?php dd($var) ?>` | Remove before commit |
| 12 | Wrong content class | `content-header` | `app-content-header` |
| 13 | Missing `nav-icon` in sidebar | `<i class="fas fa-home"></i>` | `<i class="nav-icon fas fa-home"></i>` |
| 14 | Table without `thead` | Only `<tbody>` | Always include `<thead>` |
| 15 | Missing `title` on icon buttons | `<a class="btn"><i class="fas fa-edit"></i></a>` | Add `title="Edit"` |

---

## 8. Accessibility Checklist

| Rule | Implementation |
|------|---------------|
| Form labels | Every `<input>`/`<select>`/`<textarea>` must have `<label for="">` |
| Icon-only buttons | Always add `title` attribute |
| Alert dismiss button | Use `aria-label="Close"` on `btn-close` |
| Modal | Add `aria-labelledby` pointing to title, `aria-hidden="true"` |
| Breadcrumb | Wrap in `<nav aria-label="breadcrumb">` |
| Color contrast | Do not convey info through color alone — use icon + text |

---

**Reference:** AdminLTE 4: https://adminlte-v4.netlify.app/docs/ | Bootstrap 5.3: https://getbootstrap.com/docs/5.3/ | FA6: https://fontawesome.com/search?m=free
