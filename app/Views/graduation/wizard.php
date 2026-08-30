<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">Verifikasi Mahasiswa</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('graduation') ?>">PISN Graduation</a></li>
                        <li class="breadcrumb-item active">Verifikasi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="mb-2 text-muted">Mahasiswa ke-<strong><?= esc($index + 1) ?></strong> dari <strong><?= esc($total) ?></strong></div>

        <?php if ($error): ?>
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <i class="fas fa-triangle-exclamation"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('graduation/step') ?>" method="post">
            <?= csrf_field() ?>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-id-card"></i> 1. Identitas (cocok dengan KTP)</div>
                <div class="card-body">
                    <?php if ($identity === null): ?>
                        <div class="text-muted">Data identitas tidak dapat dimuat.</div>
                    <?php else: ?>
                        <table class="table table-sm table-bordered w-auto">
                            <tbody>
                                <?php foreach ($identity as $key => $value): ?>
                                    <tr><th><?= esc($key) ?></th><td><?= esc($value) ?></td></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="identity_ok" name="identity_ok" value="1" <?= !empty($student['identity_ok']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="identity_ok">Nama &amp; jenis kelamin sesuai KTP</label>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-chalkboard-teacher"></i> 2. Akademik (status, IPK, SKS)</div>
                <div class="card-body">
                    <?php if ($academic === null): ?>
                        <div class="text-muted">Data aktivitas kuliah tidak dapat dimuat.</div>
                    <?php elseif (empty($academic)): ?>
                        <div class="text-muted">Tidak ada aktivitas kuliah untuk NIM ini.</div>
                    <?php else: ?>
                        <div class="table-responsive mb-2">
                            <?php
                            $editableCols = ['id_status_mahasiswa', 'ips', 'ipk'];
                        $acCols = array_keys($academic[0]);
                        ?>
                            <table class="table table-sm table-bordered table-striped">
                                <thead><tr><?php foreach ($acCols as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
                                <tbody>
                                    <?php foreach ($academic as $row): ?>
                                        <tr>
                                            <?php foreach ($acCols as $col): ?>
                                                <td>
                                                    <?php if ($col === 'id_status_mahasiswa'): ?>
                                                        <?php
                                                        $stSel = $student['academics'][$row['id_semester']]['id_status_mahasiswa']
                                                            ?? $row['id_status_mahasiswa'] ?? '';
                                                        $optionsHtml = '<option value="">—</option>';
                                                        foreach (($statusOptions ?? []) as $code => $label) {
                                                            $sel = ($stSel === $code) ? ' selected' : '';
                                                            $optionsHtml .= '<option value="' . esc($code) . '"' . $sel . '>'
                                                                . esc($label) . '</option>';
                                                        }
                                                        ?>
                                                        <select class="form-select form-select-sm"
                                                                name="academics[<?= esc($row['id_semester']) ?>][id_status_mahasiswa]"><?= $optionsHtml ?></select>
                                                    <?php elseif (in_array($col, $editableCols, true)): ?>
                                                        <input type="text" class="form-control form-control-sm"
                                                               name="academics[<?= esc($row['id_semester']) ?>][<?= esc($col) ?>]"
                                                               value="<?= esc($student['academics'][$row['id_semester']][$col] ?? $row[$col] ?? '') ?>">
                                                    <?php else: ?>
                                                        <?= esc($row[$col] ?? '') ?>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-clipboard-check"></i> 2b. Kelengkapan Transkrip</div>
                <div class="card-body">
                    <?php if ($transcript === null): ?>
                        <div class="text-muted">Data transkrip tidak dapat dimuat.</div>
                    <?php else: ?>
                        <?php if (! empty($completeness['complete'])): ?>
                            <div class="alert alert-success py-2 mb-2">
                                <i class="fas fa-circle-check"></i> Transkrip lengkap — nilai skripsi/tugas akhir tersedia.
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 mb-2">
                                <i class="fas fa-triangle-exclamation"></i> Transkrip belum lengkap: <?= esc($completeness['reason'] ?? 'periksa nilai.') ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted small">Jumlah nilai terload: <?= esc(count($transcript)) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-certificate"></i> 3. Eligibilitas PISN</div>
                <div class="card-body">
                    <?php if (! empty($pisn['available'])): ?>
                        <div class="text-info">Eligibilitas: <?= $pisn['eligible'] ? 'Ya' : 'Tidak' ?></div>
                    <?php else: ?>
                        <div class="text-muted"><i class="fas fa-circle-info"></i> <?= esc($pisn['reason']) ?></div>
                    <?php endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="pisn_ok" name="pisn_ok" value="1" <?= !empty($student['pisn_ok']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pisn_ok">Sudah dicek secara manual</label>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-graduation-cap"></i> 4. Input Kelulusan</div>
                <div class="card-body">
                    <?php
                        $g = $student['graduation']
                        ?? ['nim' => $student['nim'], 'nama' => $student['nama'], 'jenis_keluar' => $student['jenis_keluar'], 'tgl_keluar' => $student['tgl_keluar'], 'periode_keluar' => $student['periode_keluar'], 'ipk' => $student['ipk'], 'no_ijazah' => '-'];
                        ?>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">NPM / NIM</label><input class="form-control" name="nim" value="<?= esc($g['nim']) ?>" required></div>
                        <div class="col-md-8"><label class="form-label">Nama</label><input class="form-control" name="nama" value="<?= esc($g['nama']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Jenis Keluar</label><input class="form-control" name="jenis_keluar" value="<?= esc($g['jenis_keluar']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Tanggal Keluar/Lulus</label><input class="form-control" name="tgl_keluar" value="<?= esc($g['tgl_keluar']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Periode Keluar</label><input class="form-control" name="periode_keluar" value="<?= esc($g['periode_keluar']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">IPK</label><input class="form-control" name="ipk" value="<?= esc($g['ipk']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">No Ijazah / No Sertifikat Profesi</label><input class="form-control" name="no_ijazah" value="<?= esc($g['no_ijazah']) ?>"></div>
                    </div>
                    <div class="form-text">No Ijazah otomatis &quot;-&quot; bila dikosongkan; nomor ijazah dibuat di aplikasi PISN setelah sinkronisasi.</div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="<?= base_url('graduation') ?>" class="btn btn-outline-secondary">Batal</a>
<button type="submit" class="btn btn-primary">
                    Berikutnya <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>
</div>
