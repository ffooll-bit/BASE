<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BASE | Log in</title>
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="<?= base_url('fontawesome/css/all.min.css') ?>">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
    <!-- AdminLTE 4 Theme style -->
    <link rel="stylesheet" href="<?= base_url('adminlte/css/adminlte.min.css') ?>">
    <!-- App custom styles -->
    <link rel="stylesheet" href="<?= base_url('css/app.css') ?>">
</head>
<body class="login-page bg-body-secondary">

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
                    <label for="login-username" class="visually-hidden">Email or Username</label>
                    <input type="text" name="username" id="login-username" class="form-control" placeholder="Email or Username" required autofocus autocomplete="username">
                    <div class="input-group-text">
                        <span class="fas fa-at"></span>
                    </div>
                </div>

                <div class="input-group mb-3">
                    <label for="login-password" class="visually-hidden">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password" id="login-password">
                    <button type="button" class="input-group-text" id="toggle-password" tabindex="-1" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-8">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                    </div>
                    <div class="col-4 text-end">
                        <a href="#" class="text-decoration-none text-muted small d-none">Forgot password?</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary w-100">Sign in</button>
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
<!-- App custom scripts -->
<script src="<?= base_url('js/app.js') ?>"></script>
<script>
document.getElementById('toggle-password')?.addEventListener('click', function () {
    const input = document.getElementById('login-password');
    const icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
});
</script>
</body>
</html>
