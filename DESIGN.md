# DESIGN.md — UI/UX Design System

This project uses **AdminLTE 4** (built on **Bootstrap 5.3**) with **Font Awesome 6** icons.
This document is the single source of truth for UI markup — every view must match these patterns.


---

## 1. Layout Anatomy

The page is assembled in the controller from four partials concatenated in order:

```
view('layout/header')
+ view('layout/sidebar')
+ view('{page}')          // page-specific content
+ view('layout/footer')
```

(See `ARCHITECTURE.md` for the controller-side implementation.)

### 1.1 Body element

```html
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
```

| Class | Purpose |
|-------|---------|
| `layout-fixed` | Sidebar gets its own scrollbar; only the main content scrolls |
| `sidebar-expand-lg` | Sidebar breakpoint at `lg` (≥992px). Below that, sidebar becomes off-canvas |
| `bg-body-tertiary` | Bootstrap 5 page background |

### 1.2 Page DOM structure

```html
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <div class="app-wrapper">

    <!-- Header -->
    <nav class="app-header navbar navbar-expand bg-body">...</nav>

    <!-- Sidebar -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">...</aside>

    <!-- Main content -->
    <main class="app-main">...</main>

    <!-- Footer -->
    <footer class="app-footer">...</footer>

  </div>
</body>
```

| Region | Tag | Class |
|--------|-----|-------|
| Wrapper | `div` | `app-wrapper` |
| Header | `nav` | `app-header navbar navbar-expand bg-body` |
| Sidebar | `aside` | `app-sidebar bg-body-secondary shadow` + `data-bs-theme="dark"` |
| Content | `main` | `app-main` |
| Footer | `footer` | `app-footer` |

### 1.3 Partial: header.php

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
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="fas fa-user"></i> <?= esc($username) ?>
                </a>
                <div class="dropdown-menu dropdown-menu-end">
                    <form action="<?= base_url('logout') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
```

### 1.4 Partial: sidebar.php

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

### 1.5 Partial: footer.php

```html
    </main>

    <footer class="app-footer">
        <strong>&copy; <?= date('Y') ?> BASE - Bongaya Advanced Services Engine.</strong>
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

Note: `float-sm-end` is the Bootstrap 5 equivalent of the Bootstrap 4 `float-sm-right`.

---

## 2. Navigation

### 2.1 Basic menu item

```html
<li class="nav-item">
    <a href="<?= base_url('dashboard') ?>" class="nav-link">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>
```

- Always use `nav-icon` class on the icon inside the sidebar.
- Icon placement: `<i class="nav-icon fas|far fa-*"></i>` before `<p>`.

### 2.2 Active state

```html
<li class="nav-item">
    <a href="<?= base_url('current-page') ?>" class="nav-link active">
        <i class="nav-icon fas fa-..."></i>
        <p>Current Page</p>
    </a>
</li>
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

- The parent `<ul>` must have `data-lte-toggle="treeview"` for the collapse to work.
- Submenu items use `far fa-circle` (or `fas fa-circle`) as the nav icon — smaller and visually nested.
- The arrow icon that indicates a collapsible parent: `fas fa-angle-right` with class `nav-arrow`.

### 2.4 Menu section header

```html
<li class="nav-header">MAIN NAVIGATION</li>
```

Use `nav-header` for visual grouping labels between menu sections.

### 2.5 Menu open state

```html
<li class="nav-item menu-open">
    <a href="#" class="nav-link active">
        <i class="nav-icon fas fa-folder-open"></i>
        <p>
            Parent Menu
            <i class="nav-arrow fas fa-angle-right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        ...
    </ul>
</li>
```

Add `menu-open` to the parent `li` when a child page is active and the treeview should be expanded.

### 2.6 User panel

```html
<div class="user-panel mt-3 pb-3 mb-3 d-flex">
    <div class="image">
        <span class="img-circle elevation-2 bg-info d-flex align-items-center justify-content-center"
              style="width:34px;height:34px;">
            <i class="fas fa-user"></i>
        </span>
    </div>
    <div class="info">
        <a href="#" class="d-block"><?= esc(session('auth.username')) ?></a>
    </div>
</div>
```

Place the user panel inside `sidebar-wrapper`, before the `<nav>`.

---

## 3. Page Header

Every page view starts with `app-content-header` followed by `app-content`.

### 3.1 Standard page header

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

### 3.2 Page header with action button

```html
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="mb-0"><?= esc($title) ?></h3>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-end">
                    <a href="<?= base_url('resource/create') ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
```

Use `float-sm-end` for right-aligned action buttons in the header. Use `float-sm-end` not `float-sm-right` (Bootstrap 5 renamed it).

### 3.3 Page header with breadcrumb + action

Use two elements in `col-sm-6`:

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

## 4. Cards

Cards are Bootstrap 5 components and unchanged from the framework defaults.

### 4.1 Standard card

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= esc($title) ?></h3>
        <div class="card-tools">
            <!-- Optional: collapse, close, maximize buttons -->
            <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Main content -->
    </div>
    <div class="card-footer">
        <!-- Optional footer -->
    </div>
</div>
```

### 4.2 Card variants

| Variant | Class | When to use |
|---------|-------|------------|
| Default | `card` | Standard content sections |
| Primary | `card card-primary` | Important/warning sections |
| Success | `card card-success` | Success confirmation sections |
| Danger | `card card-danger` | Error, deletion, destructive actions |
| Info | `card card-info` | Informational sections |
| Outline | `card card-outline card-primary` | Subtle variant with colored border |
| Collapsible | `card` + `data-lte-toggle="card-collapse"` | Sections the user can collapse |

### 4.3 Card with table

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Data Table</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-bordered mb-0">
            ...
        </table>
    </div>
</div>
```

Use `p-0` on `card-body` when it wraps only a table so the table touches the card edges.

### 4.4 Info box (AdminLTE small box)

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

Available backgrounds: `bg-info`, `bg-success`, `bg-warning`, `bg-danger`, `bg-primary`.

---

## 5. Forms

### 5.1 Form wrapper

```html
<form action="<?= base_url('resource/store') ?>" method="post">
    <?= csrf_field() ?>
    <!-- form fields -->
</form>
```

- Method is always `post` for data mutations. Use `method="get"` only for search/filter forms.
- `csrf_field()` is **required** immediately after the form open tag.

### 5.2 Input group with icon (for login / search)

```html
<div class="input-group mb-3">
    <input type="email" name="email" class="form-control"
           placeholder="Email" required autofocus autocomplete="email">
    <div class="input-group-text">
        <span class="fas fa-envelope"></span>
    </div>
</div>
```

### 5.3 Form group with label (standard pattern for most forms)

```html
<div class="mb-3">
    <label for="fieldName" class="form-label">Field Label</label>
    <input type="text" name="fieldName" id="fieldName"
           class="form-control" value="<?= esc($oldValue) ?>"
           placeholder="Enter value" required>
</div>
```

- Always use `form-label` class on labels (Bootstrap 5 requires this for proper styling).
- The `for` attribute must match the `id` on the input.

### 5.4 Validation errors

```html
<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" name="email" id="email"
           class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
           value="<?= esc(old('email')) ?>">
    <?php if (session('errors.email')): ?>
        <div class="invalid-feedback"><?= esc(session('errors.email')) ?></div>
    <?php endif; ?>
</div>
```

- `is-invalid` class on the input triggers red border.
- `invalid-feedback` div shows the error message below the input.
- Use `esc()` on error messages from session.

### 5.5 Select dropdown

```html
<div class="mb-3">
    <label for="selectField" class="form-label">Options</label>
    <select name="selectField" id="selectField" class="form-select">
        <option value="">-- Select --</option>
        <?php foreach ($options as $value => $label): ?>
            <option value="<?= esc($value) ?>"
                <?= $selected === $value ? 'selected' : '' ?>>
                <?= esc($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
```

- Use `form-select` class (not `form-control`) for `<select>` elements.

### 5.6 Textarea

```html
<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea name="description" id="description"
              class="form-control" rows="3"><?= esc($value) ?></textarea>
</div>
```

### 5.7 Checkbox / Switch

```html
<div class="mb-3">
    <div class="form-check form-switch">
        <input type="checkbox" name="is_active" id="is_active"
               class="form-check-input" value="1"
               <?= $isActive ? 'checked' : '' ?>>
        <label for="is_active" class="form-check-label">Active</label>
    </div>
</div>
```

### 5.8 Submit buttons

```html
<div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Save
    </button>
    <a href="<?= base_url('resource') ?>" class="btn btn-secondary">
        <i class="fas fa-times"></i> Cancel
    </a>
</div>
```

### 5.9 Inline form (search)

```html
<form action="<?= base_url('resource') ?>" method="get" class="row g-3 align-items-center">
    <div class="col-auto">
        <input type="text" name="q" class="form-control"
               placeholder="Search..." value="<?= esc($search) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>
        </button>
    </div>
</form>
```

### 5.10 Validation error summary

```html
<?php if (session()->has('errors')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <h5><i class="fas fa-exclamation-triangle"></i> Validation Error</h5>
        <ul class="mb-0">
            <?php foreach (session('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
```

---

## 6. Tables

### 6.1 Standard data table

```html
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th><?= esc($columnLabel) ?></th>
                <th class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="3" class="text-center text-muted">No data available</td>
                </tr>
            <?php else: ?>
                <?php $i = 1; foreach ($items as $item): ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= esc($item->name) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= base_url('resource/edit/' . $item->id) ?>"
                           class="btn btn-sm btn-info" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger btn-delete"
                                data-id="<?= $item->id ?>"
                                data-name="<?= esc($item->name, 'attr') ?>"
                                title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
```

### 6.2 Table inside card (with header)

```html
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><?= esc($title) ?></h3>
        <div class="card-tools">
            <a href="<?= base_url('resource/create') ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-striped table-bordered mb-0">
            ...
        </table>
    </div>
    <div class="card-footer clearfix">
        <?= $pager ?? '' ?>
    </div>
</div>
```

### 6.3 Table classes

| Class | When to use |
|-------|------------|
| `table` | Required base class |
| `table-striped` | Zebra-striping for readability |
| `table-bordered` | Borders on all cells |
| `table-hover` | Highlight row on hover |
| `table-sm` | Compact table (smaller padding) |
| `mb-0` | On table when inside `card-body.p-0` (removes bottom margin) |
| `text-nowrap` | On action `td` to prevent button wrapping |
| `table-responsive` | On wrapper `div` to enable horizontal scroll on small screens |

---

## 7. Alerts & Flash Messages

### 7.1 Alert types

```html
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?= esc(session()->getFlashdata('error')) ?>
    </div>
<?php endif; ?>
```

### 7.2 Flashdata key conventions

| Flashdata key | Alert class | Purpose |
|---------------|-------------|---------|
| `error` | `alert-danger` | Validation errors, login failure, operation failed |
| `message` | `alert-info` | Info, session expired, notifications |
| `success` | `alert-success` | Operation succeeded (created, updated, deleted) |
| `warning` | `alert-warning` | Confirmations, caution |

### 7.3 Alert placement

Place alerts at the **top of `app-content`**, before the first card:

```html
<div class="app-content">
    <div class="container-fluid">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible">...</div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible">...</div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('warning')): ?>
            <div class="alert alert-warning alert-dismissible">...</div>
        <?php endif; ?>

        <div class="card">...</div>

    </div>
</div>
```

### 7.4 Alert with icon

```html
<div class="alert alert-success alert-dismissible d-flex align-items-center gap-2">
    <i class="fas fa-check-circle"></i>
    <div><?= esc(session()->getFlashdata('success')) ?></div>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
```

### 7.5 Inline confirmation alert (non-dismissible)

```html
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    Are you sure you want to delete this item? This action cannot be undone.
</div>
```

---

## 8. Buttons

### 8.1 Button variants

| Variant | Class | Use case |
|---------|-------|----------|
| Primary | `btn btn-primary` | Main action (save, submit, create) |
| Secondary | `btn btn-secondary` | Cancel, back, discard |
| Success | `btn btn-success` | Confirm, approve, activate |
| Danger | `btn btn-danger` | Delete, deactivate, revoke |
| Info | `btn btn-info` | Edit, view, detail |
| Warning | `btn btn-warning` | Flag, suspend, caution |
| Outline | `btn btn-outline-primary` | Secondary actions, less emphasis |
| Tool | `btn btn-tool` | Card collapse/close/maximize buttons |

### 8.2 Button sizes

| Size | Class | When to use |
|------|-------|------------|
| Default | `btn` | Standard buttons in forms |
| Small | `btn btn-sm` | Table action rows, card-tools |
| Large | `btn btn-lg` | Prominent CTA on empty states |

### 8.3 Icon + text pattern

```html
<button type="submit" class="btn btn-primary">
    <i class="fas fa-save"></i> Save
</button>

<a href="<?= base_url('resource') ?>" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
```

### 8.4 Icon-only button (table actions)

```html
<a href="<?= base_url('resource/edit/' . $item->id) ?>" class="btn btn-sm btn-info" title="Edit">
    <i class="fas fa-edit"></i>
</a>
<button type="button" class="btn btn-sm btn-danger" title="Delete">
    <i class="fas fa-trash"></i>
</button>
```

Always include `title` attribute on icon-only buttons for accessibility.

### 8.5 Button group

```html
<div class="btn-group">
    <a href="#" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
    <a href="#" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
    <button type="button" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
</div>
```

---

## 9. Modals

### 9.1 Standard modal

```html
<div class="modal fade" id="modalName" tabindex="-1" aria-labelledby="modalNameLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNameLabel"><?= esc($title) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Modal content here.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>
```

### 9.2 Modal sizes

```html
<!-- Small -->
<div class="modal-dialog modal-sm">...</div>

<!-- Default (medium) -->
<div class="modal-dialog">...</div>

<!-- Large -->
<div class="modal-dialog modal-lg">...</div>

<!-- Extra large -->
<div class="modal-dialog modal-xl">...</div>
```

### 9.3 Delete confirmation modal

```html
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteItemName"></strong>?</p>
                <p class="text-muted mb-0">This action cannot be undone.</p>
                <form id="deleteForm" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
```

### 9.4 Trigger modal from JavaScript (vanilla JS)

```html
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-delete').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var name = this.getAttribute('data-name');
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('deleteForm').action = '<?= base_url('resource/delete') ?>/' + id;
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function () {
        document.getElementById('deleteForm').submit();
    });
});
</script>
```

> No jQuery. Use `bootstrap.Modal` constructor from Bootstrap 5's bundled JS.
> Delete buttons use `data-id`/`data-name` attributes and `addEventListener` — no `onclick` attributes.

---

## 10. Icons

Project uses **Font Awesome 6** (`fas` for solid, `far` for regular).

### 10.1 Purpose-to-icon reference

| Purpose | Icon class |
|---------|-----------|
| Dashboard | `fas fa-tachometer-alt` |
| Add / Create | `fas fa-plus` |
| Edit | `fas fa-edit` |
| Delete | `fas fa-trash` |
| View / Detail | `fas fa-eye` |
| Save | `fas fa-save` |
| Search | `fas fa-search` |
| Back / Return | `fas fa-arrow-left` |
| Forward / Next | `fas fa-arrow-right` |
| Download | `fas fa-download` |
| Upload | `fas fa-upload` |
| Export | `fas fa-file-export` |
| Print | `fas fa-print` |
| Filter | `fas fa-filter` |
| Refresh | `fas fa-sync` |
| User / Profile | `fas fa-user` |
| Users | `fas fa-users` |
| Settings | `fas fa-cog` |
| Logout | `fas fa-sign-out-alt` |
| Login | `fas fa-sign-in-alt` |
| Menu / Toggle | `fas fa-bars` |
| Close / Cancel | `fas fa-times` |
| Check / Yes | `fas fa-check` |
| Warning | `fas fa-exclamation-triangle` |
| Info | `fas fa-info-circle` |
| Success | `fas fa-check-circle` |
| Error | `fas fa-times-circle` |
| Mail / Email | `fas fa-envelope` |
| Lock / Password | `fas fa-lock` |
| Calendar | `fas fa-calendar` |
| Clock / Time | `fas fa-clock` |
| Home | `fas fa-home` |
| Folder | `fas fa-folder` |
| File | `fas fa-file` |
| Chart / Report | `fas fa-chart-bar` |
| Notification | `fas fa-bell` |
| Upload Image | `fas fa-image` |

### 10.2 Icon style conventions

- **Solid (`fas`)** — default for navigation icons, buttons, alerts, form icons
- **Regular (`far`)** — for nested sidebar child icons (`far fa-circle`) and lower-emphasis UI
- **Brands (`fab`)** — only for third-party logos (e.g., `fab fa-google`)

### 10.3 Icon placement rules

| Context | Class | Example |
|---------|-------|---------|
| Sidebar menu | `nav-icon` | `<i class="nav-icon fas fa-..."></i>` |
| Button (icon + text) | none | `<i class="fas fa-..."></i> Label` |
| Button (icon only) | none, use `title` attr | `<i class="fas fa-..." title="Edit"></i>` |
| Input group | inside `input-group-text` | `<div class="input-group-text"><span class="fas fa-..."></span></div>` |
| Alert | before text | `<i class="fas fa-..."></i> Message` |
| Card tools | none | `<button class="btn btn-tool"><i class="fas fa-..."></i></button>` |

---

## 11. Responsive Rules

### 11.1 Body classes

```html
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
```

| Class | Effect |
|-------|--------|
| `layout-fixed` | Fixed sidebar height — only `app-main` scrolls |
| `sidebar-expand-lg` | Sidebar is inline at ≥992px, off-canvas below that |

### 11.2 Sidebar behavior by breakpoint

| Screen size | Sidebar state | Toggle behavior |
|-------------|--------------|-----------------|
| ≥992px (`lg`) | Inline (visible in grid) | Toggle collapses to icon state |
| <992px | Off-canvas (hidden) | Toggle slides in with overlay |

### 11.3 Table responsiveness

Always wrap `<table>` in `<div class="table-responsive">` — enables horizontal scroll on small screens.

### 11.4 Form responsiveness

| Pattern | Default | Mobile |
|---------|---------|--------|
| Form groups | `col-sm-6` for 2-column | Stack to full width |
| Button row | `d-flex gap-2` | Stays horizontal (small buttons) |
| Search form | `row g-3 align-items-center` | Input + button on same line |

### 11.5 Grid rules

- Use `col-sm-*` (not `col-md-*`) as the default breakpoint for two-column layouts
- Use `col-12` as the mobile default, override with `col-sm-6` for multi-column
- Never use hardcoded widths (pixels) on content elements — use Bootstrap sizing utilities

### 11.6 Navbar

- The "Home" link uses `d-none d-sm-inline-block` to hide on extra-small screens
- The user dropdown is always visible (no responsive classes needed)

### 11.7 Mobile-specific classes (applied by AdminLTE JS)

| Class | Applied to | When |
|-------|-----------|------|
| `sidebar-open` | `body` | Sidebar is open on mobile (with overlay) |
| `sidebar-collapse` | `body` | Sidebar is closed (set automatically) |

These are managed by AdminLTE PushMenu plugin. Do not toggle them manually.

---

## 12. Anti-Patterns

These are common mistakes. **Do not commit these.**

| # | Mistake | Wrong | Correct |
|---|---------|-------|---------|
| 1 | AdminLTE 3 classes | `<div class="wrapper">` | `<div class="app-wrapper">` |
| 2 | Missing `esc()` | `<?= $username ?>` | `<?= esc($username) ?>` |
| 3 | Using jQuery | `$(document).ready()` | vanilla JS or `data-*` attributes |
| 4 | `data-*` instead of `data-bs-*` | `data-toggle="dropdown"` | `data-bs-toggle="dropdown"` |
| 5 | AdminLTE 3 sidebar classes | `nav-sidebar` | `sidebar-menu` |
| 6 | Bootstrap 4 utility classes | `ml-auto`, `float-right` | `ms-auto`, `float-end` |
| 7 | `form-control` on select | `<select class="form-control">` | `<select class="form-select">` |
| 8 | Missing `form-label` | `<label>Field</label>` | `<label class="form-label">Field</label>` |
| 9 | Missing `csrf_field()` | `<form method="post">` without CSRF | `<?= csrf_field() ?>` after form open |
| 10 | Inline styles for layout | `style="width:250px"` on sidebar | Use AdminLTE SCSS variables |
| 11 | `dd()` / `var_dump()` | Debug output in views | Remove before commit |
| 12 | Breadcrumb with wrong class | `content-header` instead of `app-content-header` | `app-content-header` |
| 13 | Wrong menu icon class | `<i class="fas fa-home"></i>` in sidebar | `<i class="nav-icon fas fa-home"></i>` |
| 14 | Table without `thead` | Only `<tbody>` | Always include `<thead>` |
| 15 | Missing `title` on icon buttons | `<a class="btn btn-sm"><i class="fas fa-edit"></i></a>` | Add `title="Edit"` |

---

## 13. Accessibility Checklist

Follow these minimum a11y rules for every new view or component:

| Rule | Implementation |
|------|---------------|
| Form labels | Every `<input>`, `<select>`, `<textarea>` must have a `<label>` with `for` attribute |
| Icon-only buttons | Always add `title` attribute describing the action |
| Alert dismiss button | Use `aria-label="Close"` on `btn-close` |
| Modal | Add `aria-labelledby` pointing to modal title, `aria-hidden="true"` |
| Breadcrumb | Wrap in `<nav>` with `aria-label="breadcrumb"` |
| Color contrast | Do not convey information through color alone — use icons + text (e.g., `text-danger` + icon) |
| Keyboard navigation | All interactive elements must be reachable via Tab. No `tabindex="-1"` on standard controls. |
| Focus management | After modal close, return focus to the trigger element |

---

**Reference:**
- AdminLTE 4 docs: https://adminlte-v4.netlify.app/docs/
- Bootstrap 5.3 docs: https://getbootstrap.com/docs/5.3/
- Font Awesome 6 cheatsheet: https://fontawesome.com/search?m=free
- Bootstrap Icons (alternative): https://icons.getbootstrap.com/
