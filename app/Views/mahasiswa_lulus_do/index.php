<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">Daftar Mahasiswa Lulus / Dropout</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Mahasiswa Lulus / DO</li>
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
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="nama_mahasiswa" placeholder="Nama Mahasiswa" value="<?= esc($filters['nama_mahasiswa'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="id_jenis_keluar" placeholder="ID Jenis Keluar" value="<?= esc($filters['id_jenis_keluar'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Cari</button>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label d-flex align-items-center gap-2 mb-0">
                            <span class="small text-muted">Baris/halaman</span>
                            <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                <?php foreach ([10, 20, 50, 100] as $sz): ?>
                                    <option value="<?= $sz ?>" <?= $pageSize == $sz ? 'selected' : '' ?>><?= $sz ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($rows === null && !$error): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Masukkan filter untuk menampilkan data mahasiswa lulus/dropout.
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
                                    <?php foreach (array_keys($rows[0]) as $col): ?><th><?= esc($labels[$col] ?? $col) ?></th><?php endforeach; ?>
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
                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <?php
                    $prevDisabled = $page <= 1 ? 'disabled' : '';
                        if ($totalPages !== null) {
                            $nextDisabled = $page >= $totalPages ? 'disabled' : '';
                            $lastPage = $totalPages;
                            $pageLabel = "Halaman {$page} dari {$totalPages}";
                        } else {
                            $nextDisabled = is_array($rows) && count($rows) < $pageSize ? 'disabled' : '';
                            $lastPage = $page + 1;
                            $pageLabel = "Halaman {$page}";
                        }
                        $firstQ = http_build_query(array_merge($filters, ['page' => 1, 'per_page' => $pageSize]));
                        $prevQ  = http_build_query(array_merge($filters, ['page' => max(1, $page - 1), 'per_page' => $pageSize]));
                        $nextQ  = http_build_query(array_merge($filters, ['page' => $page + 1, 'per_page' => $pageSize]));
                        $lastQ  = http_build_query(array_merge($filters, ['page' => $lastPage, 'per_page' => $pageSize]));
                        ?>
                    <nav aria-label="Navigasi halaman">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $prevDisabled ?>">
                                <a class="page-link" href="?<?= esc($firstQ, 'html') ?>">« Pertama</a>
                            </li>
                            <li class="page-item <?= $prevDisabled ?>">
                                <a class="page-link" href="?<?= esc($prevQ, 'html') ?>">‹ Sebelumnya</a>
                            </li>
                            <li class="page-item disabled"><span class="page-link"><?= esc($pageLabel) ?></span></li>
                            <li class="page-item <?= $nextDisabled ?>">
                                <a class="page-link" href="?<?= esc($nextQ, 'html') ?>">Berikutnya ›</a>
                            </li>
                            <li class="page-item <?= $nextDisabled ?>">
                                <a class="page-link" href="?<?= esc($lastQ, 'html') ?>">Terakhir »</a>
                            </li>
                        </ul>
                    </nav>
                    <span class="text-muted small">Total: <?= $total !== null ? esc($total) . ' data' : '—' ?></span>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
