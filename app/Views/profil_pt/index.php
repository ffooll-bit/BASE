<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">Profil Perguruan Tinggi</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Profil Perguruan Tinggi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <i class="fas fa-times-circle"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($profilPT === null && !$error): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Silakan login untuk melihat data profil perguruan tinggi.
            </div>
        <?php endif; ?>

        <?php if ($profilPT): ?>

        <div class="row justify-content-center">
            <div class="col-xxl-10">

                <div class="card card-outline card-primary mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h2 class="display-6 mb-1"><?= esc($profilPT['nama_perguruan_tinggi']) ?></h2>
                                <div class="text-muted small mb-1">Kode PT: <?= esc($profilPT['kode_perguruan_tinggi'] ?? '-') ?></div>
                                <div class="text-muted small">Kepemilikan: <?= esc($profilPT['nama_status_milik'] ?? '-') ?></div>
                            </div>
                            <span class="badge bg-success fs-6">Akreditasi <?= esc($profilPT['status_perguruan_tinggi'] ?? '-') ?></span>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex gap-4 small">
                            <div><span class="text-muted">SK Pendirian:</span> <?= esc($profilPT['sk_pendirian'] ?? '-') ?></div>
                            <div><span class="text-muted">Tanggal SK:</span> <?= esc($tanggalSK) ?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-address-card"></i> Kontak</h3>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <div class="fs-6 mb-2">
                                    <i class="fas fa-phone text-muted me-2 fa-fw"></i> <?= esc($profilPT['telepon'] ?? '-') ?>
                                </div>
                                <div class="fs-6 mb-2">
                                    <i class="fas fa-fax text-muted me-2 fa-fw"></i> <?= esc($profilPT['faximile'] ?? '-') ?>
                                </div>
                                <div class="fs-6 mb-2">
                                    <i class="fas fa-envelope text-muted me-2 fa-fw"></i>
                                    <?php if (!empty($profilPT['email'])): ?>
                                        <a href="mailto:<?= esc($profilPT['email'], 'url') ?>"><?= esc($profilPT['email']) ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                                <div class="fs-6 mt-auto">
                                    <i class="fas fa-globe text-muted me-2 fa-fw"></i>
                                    <?php if ($website !== '-'): ?>
                                        <a href="<?= esc($website, 'url') ?>" target="_blank"><?= esc($profilPT['website'] ?? $website) ?></a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Alamat</h3>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <p class="fs-6 mb-1"><?= esc($profilPT['jalan'] ?? '-') ?></p>
                                <p class="fs-6 mb-1"><?= esc($profilPT['kelurahan'] ?? '-') ?></p>
                                <p class="fs-6 mb-1"><?= esc($profilPT['nama_wilayah'] ?? '-') ?></p>
                                <p class="fs-6 mb-0 mt-auto">Kode Pos: <?= esc($profilPT['kode_pos'] ?? '-') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php endif; ?>

    </div>
</div>
