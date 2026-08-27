<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">PISN Graduation — Hasil</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">PISN Graduation</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="alert alert-success">
            <i class="fas fa-circle-check"></i> Verifikasi selesai. Berikut ringkasan pengiriman ke Neo Feeder.
        </div>

        <?php if (empty($results)): ?>
            <div class="alert alert-info">Tidak ada data dikirim.</div>
        <?php else: ?>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>NIM</th><th>Status</th><th>Keterangan</th></tr></thead>
                        <tbody>
                            <?php foreach ($results as $r): ?>
                                <tr>
                                    <td><?= esc($r['nim']) ?></td>
                                    <td><?= $r['success'] ? '<span class="badge text-bg-success">Terkirim</span>' : '<span class="badge text-bg-danger">Gagal</span>' ?></td>
                                    <td><?= esc($r['msg']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><i class="fas fa-info-circle"></i> Panduan Pasca Kelulusan</div>
            <div class="card-body">
                <ol>
                    <li>Tunggu sinkronisasi PISN (1×24 jam) sebelum membuat nomor ijazah.</li>
                    <li>Buat nomor ijazah di aplikasi web PISN setelah data tersinkron.</li>
                    <li>Bila ada pengiriman gagal, perbaiki data lalu ulangi proses untuk mahasiswa terkait.</li>
                </ol>
                <a href="<?= base_url('graduation') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Proses Batch Baru</a>
            </div>
        </div>
    </div>
</div>
