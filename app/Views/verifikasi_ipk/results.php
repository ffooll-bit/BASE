<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Verifikasi IPK — Hasil</h1>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="alert alert-success">
            <i class="fas fa-circle-check"></i> Verifikasi selesai. Berikut ringkasan pengiriman ke Neo Feeder.
        </div>

        <?php if (empty($results)): ?>
            <div class="alert alert-info">Tidak ada data diproses.</div>
        <?php else: ?>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>NIM</th><th>Status</th><th>Keterangan</th></tr></thead>
                        <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= esc($r['nim']) ?></td>
                                <td><?= ! empty($r['success']) ? '<span class="badge text-bg-success">Berhasil</span>' : '<span class="badge text-bg-danger">Gagal</span>' ?></td>
                                <td><?= esc($r['msg']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <a href="<?= base_url('verifikasi-ipk') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Verifikasi Baru</a>
            </div>
        </div>
    </div>
</div>
