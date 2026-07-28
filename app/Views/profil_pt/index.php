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

        <div class="card card-outline card-primary">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h2 class="card-title h2 mb-1"><?= esc($profilPT['nama_perguruan_tinggi']) ?></h2>
                        <div class="text-muted">
                            Kode PT: <?= esc($profilPT['kode_perguruan_tinggi'] ?? '-') ?>
                            &middot; <?= esc($profilPT['nama_status_milik'] ?? '-') ?>
                        </div>
                    </div>
                    <span class="badge bg-success fs-6"><?= esc($profilPT['status_perguruan_tinggi'] ?? '-') ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-address-card"></i> Kontak</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-2"><i class="fas fa-phone text-muted me-2" style="width:1.25rem"></i> <?= esc($profilPT['telepon'] ?? '-') ?></div>
                        <div class="mb-2"><i class="fas fa-fax text-muted me-2" style="width:1.25rem"></i> <?= esc($profilPT['faximile'] ?? '-') ?></div>
                        <div class="mb-2"><i class="fas fa-envelope text-muted me-2" style="width:1.25rem"></i>
                            <?php if (!empty($profilPT['email'])): ?>
                                <a href="mailto:<?= esc($profilPT['email'], 'url') ?>"><?= esc($profilPT['email']) ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                        <div class="mb-0"><i class="fas fa-globe text-muted me-2" style="width:1.25rem"></i>
                            <?php if (!empty($profilPT['website'])): ?>
                                <a href="<?= esc($profilPT['website'], 'url') ?>" target="_blank"><?= esc($profilPT['website']) ?></a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Alamat</h3>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><?= esc($profilPT['jalan'] ?? '-') ?></p>
                        <p class="mb-1"><?= esc($profilPT['kelurahan'] ?? '-') ?></p>
                        <p class="mb-1"><?= esc($profilPT['nama_wilayah'] ?? '-') ?></p>
                        <p class="mb-0">Kode Pos: <?= esc($profilPT['kode_pos'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-gavel"></i> Legalitas Pendirian</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-2 text-muted fw-normal">SK Pendirian</dt>
                    <dd class="col-sm-10"><?= esc($profilPT['sk_pendirian'] ?? '-') ?></dd>
                    <dt class="col-sm-2 text-muted fw-normal">Tanggal SK</dt>
                    <dd class="col-sm-10"><?= esc($tanggalSK) ?></dd>
                </dl>
            </div>
        </div>

        <?php endif; ?>

    </div>
</div>
