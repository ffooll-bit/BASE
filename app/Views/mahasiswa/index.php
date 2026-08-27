<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">Daftar Mahasiswa</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Daftar Mahasiswa</li>
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

        <div class="card mb-3">
            <div class="card-body">
                <form method="get" class="row g-2">
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="nim" placeholder="NIM" value="<?= esc($filters['nim'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="nipd" placeholder="NIPD" value="<?= esc($filters['nipd'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="nama_mahasiswa" placeholder="Nama Mahasiswa" value="<?= esc($filters['nama_mahasiswa'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="angkatan" placeholder="Angkatan" value="<?= esc($filters['angkatan'] ?? '') ?>">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">Cari</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($rows === null && !$error): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Masukkan filter untuk menampilkan data mahasiswa.
            </div>
        <?php elseif ($rows !== null): ?>
            <div class="card">
                <div class="card-body table-responsive">
                    <?php if (empty($rows)): ?>
                        <div class="alert alert-warning mb-0">Tidak ada data yang cocok dengan filter.</div>
                    <?php else: ?>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($rows[0]) as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row): ?>
                                    <tr><?php foreach ($row as $cell): ?><td><?= esc($cell) ?></td><?php endforeach; ?></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <?php $prev = http_build_query(array_merge($filters, ['page' => max(1, $page - 1)])); ?>
                    <a href="?<?= esc($prev, 'html') ?>" class="btn btn-sm btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>">Sebelumnya</a>
                    <span>Halaman <?= esc($page) ?></span>
                    <?php $next = http_build_query(array_merge($filters, ['page' => $page + 1])); ?>
                    <a href="?<?= esc($next, 'html') ?>" class="btn btn-sm btn-outline-secondary <?= count($rows) < $pageSize ? 'disabled' : '' ?>">Berikutnya</a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
