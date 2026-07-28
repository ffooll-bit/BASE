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
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Identitas & Kontak</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td style="width:140px" class="text-muted">Nama PT</td>
                                <td><?= esc($profilPT['nama_perguruan_tinggi'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kode PT</td>
                                <td><?= esc($profilPT['kode_perguruan_tinggi'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status Akreditasi</td>
                                <td><?= esc($profilPT['status_perguruan_tinggi'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kepemilikan</td>
                                <td><?= esc($profilPT['nama_status_milik'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Telepon</td>
                                <td><?= esc($profilPT['telepon'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Fax</td>
                                <td><?= esc($profilPT['faximile'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td><?= esc($profilPT['email'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Website</td>
                                <td><?= esc($profilPT['website'] ?? '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card card-secondary">
                    <div class="card-header">
                        <h3 class="card-title">Alamat & Legalitas</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td style="width:140px" class="text-muted">Alamat</td>
                                <td><?= esc($profilPT['jalan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kelurahan</td>
                                <td><?= esc($profilPT['kelurahan'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Wilayah</td>
                                <td><?= esc($profilPT['nama_wilayah'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Kode Pos</td>
                                <td><?= esc($profilPT['kode_pos'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">SK Pendirian</td>
                                <td><?= esc($profilPT['sk_pendirian'] ?? '-') ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Tanggal SK</td>
                                <td><?= esc(isset($profilPT['tanggal_sk_pendirian']) ? date('d-m-Y', strtotime($profilPT['tanggal_sk_pendirian'])) : '-') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
