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
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">Informasi Perguruan Tinggi</h3>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0" style="table-layout:fixed">
                    <colgroup>
                        <col style="width:50%">
                        <col style="width:50%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Nama PT</th>
                            <td><?= esc($profilPT['nama_perguruan_tinggi'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Kode PT</th>
                            <td><?= esc($profilPT['kode_perguruan_tinggi'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Status Akreditasi</th>
                            <td><?= esc($profilPT['status_perguruan_tinggi'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Kepemilikan</th>
                            <td><?= esc($profilPT['nama_status_milik'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Telepon</th>
                            <td><?= esc($profilPT['telepon'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Fax</th>
                            <td><?= esc($profilPT['faximile'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Email</th>
                            <td><?= esc($profilPT['email'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Website</th>
                            <td><?= esc($profilPT['website'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Alamat</th>
                            <td><?= esc($profilPT['jalan'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Kelurahan</th>
                            <td><?= esc($profilPT['kelurahan'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Wilayah</th>
                            <td><?= esc($profilPT['nama_wilayah'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Kode Pos</th>
                            <td><?= esc($profilPT['kode_pos'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">SK Pendirian</th>
                            <td><?= esc($profilPT['sk_pendirian'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="text-muted fw-normal text-end">Tanggal SK</th>
                            <td><?= esc(isset($profilPT['tanggal_sk_pendirian']) ? date('d-m-Y', strtotime($profilPT['tanggal_sk_pendirian'])) : '-') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
