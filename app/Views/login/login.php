<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BASE | Log in</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="<?= base_url('fontawesome/css/all.min.css') ?>">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
    <!-- AdminLTE 4 Theme style -->
    <link rel="stylesheet" href="<?= base_url('adminlte/css/adminlte.min.css') ?>">
</head>
<body class="login-page">

<div class="login-box">
    <div class="login-logo">
        <a href="<?= base_url() ?>"><b>BASE</b> — Bongaya Advanced Services Engine</a>
    </div>

    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Sign in to start your session</p>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('message')): ?>
                <div class="alert alert-info alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?= esc(session()->getFlashdata('message')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="post">
                <?= csrf_field() ?>

                <div class="input-group mb-3">
                    <input type="email" name="username" class="form-control" placeholder="Email" required autofocus autocomplete="email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap 5 -->
<script src="<?= base_url('bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- AdminLTE 4 App -->
<script src="<?= base_url('adminlte/js/adminlte.min.js') ?>"></script>
</body>
</html>
