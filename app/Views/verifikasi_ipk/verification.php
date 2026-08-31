<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Verifikasi IPK</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <?php if (! empty($results)): ?>
            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-list-check"></i> Hasil batch sebelumnya</div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered mb-0">
                        <thead><tr><th>NIM</th><th>Status</th><th>Keterangan</th></tr></thead>
                        <tbody>
                        <?php foreach (array_slice($results, -25) as $r): ?>
                            <tr>
                                <td><?= esc($r['nim']) ?></td>
                                <td><?= $r['success'] ? '<span class="text-success">Berhasil</span>' : '<span class="text-danger">Gagal</span>' ?></td>
                                <td><?= esc($r['msg']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('verifikasi-ipk/apply') ?>" method="post">
            <?= csrf_field() ?>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-check"></i> Batch <?= $start + 1 ?>–<?= $nextStart ?> dari <?= $total ?> mahasiswa
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-bordered table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>IPK Neo Feeder</th>
                                <th>IPK Excel</th>
                                <th>Semester</th>
                                <th>Status</th>
                                <th style="width:70px">Perbaiki</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($rows)): ?>
                            <tr><td colspan="8" class="text-center text-muted">Tidak ada data pada batch ini.</td></tr>
                        <?php else: ?>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?= (int) $r['idx'] + 1 ?></td>
                                    <td><?= esc($r['nim']) ?></td>
                                    <td><?= esc($r['nama']) ?></td>
                                    <td><?= esc($r['neoIpk']) === '' ? '—' : esc($r['neoIpk']) ?></td>
                                    <td><?= esc($r['excelIpk']) ?></td>
                                    <td><?= esc($r['semester']) === '' ? '—' : esc($r['semester']) ?></td>
                                    <td>
                                        <?php if ($r['status'] === 'cocok'): ?>
                                            <span class="badge text-bg-success">Cocok</span>
                                        <?php elseif ($r['status'] === 'beda'): ?>
                                            <span class="badge text-bg-warning">Beda</span>
                                        <?php elseif ($r['status'] === 'non_aktif'): ?>
                                            <span class="badge text-bg-secondary">Semester non-aktif</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-danger">Tidak ditemukan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($r['editable']): ?>
                                            <input type="checkbox" name="fix[]" value="<?= esc($r['idx']) ?>">
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('verifikasi-ipk') ?>" class="btn btn-outline-secondary">Batal</a>
                    <?php if (! $isLast): ?>
                        <button type="submit" class="btn btn-primary" name="action" value="next">Perbaiki &amp; Lanjut ke Batch Berikutnya</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary" name="action" value="finish">Perbaiki &amp; Selesai</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>
