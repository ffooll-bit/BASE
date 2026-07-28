<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Server Error — BASE</title>
    <link rel="stylesheet" href="<?= base_url('bootstrap/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('fontawesome/css/all.min.css') ?>">
    <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
    <style>
        html, body { height: 100%; }
        body { display: flex; align-items: center; justify-content: center; background: #f4f6f9; font-family: system-ui, -apple-system, sans-serif; }
        .error-box { max-width: 480px; text-align: center; }
    </style>
</head>
<body>
    <div class="error-box px-3">
        <div class="mb-4">
            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem;"></i>
        </div>
        <h1 class="h2 mb-3">500 — Server Error</h1>
        <p class="text-muted mb-4">Something went wrong on our end. Please try again later.</p>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</body>
</html>
