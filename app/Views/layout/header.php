<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'BASE - Bongaya Advanced Services Engine') ?></title>
    <!-- Google Font: Source Sans 3 (AdminLTE 4) -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+3:300,400,400i,600,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('fontawesome/css/all.min.css') ?>">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
    <!-- AdminLTE 4 Theme -->
    <link rel="stylesheet" href="<?= base_url('adminlte/css/adminlte.min.css') ?>">
    <!-- App custom styles -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- Navbar -->
    <nav class="app-header navbar navbar-expand bg-body">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= base_url('dashboard') ?>" class="nav-link">Home</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ms-auto d-flex align-items-center">
            <li class="nav-item">
                <span class="nav-link text-nowrap d-none d-md-inline">
                    Signed in as: <?= esc($username ?? session('auth.username')) ?>
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
    <!-- /.navbar -->
