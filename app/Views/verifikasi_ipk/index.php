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
        <?php if (! empty($error)): ?>
            <div class="alert alert-warning py-2"><?= esc($error) ?></div>
        <?php endif; ?>

        <?php if (! empty($canResume)): ?>
            <div class="alert alert-info">
                Terdapat sesi verifikasi IPK yang belum selesai.
                <a href="<?= base_url('verifikasi-ipk/verif') ?>" class="btn btn-sm btn-primary">Lanjutkan</a>
                <form action="<?= base_url('verifikasi-ipk/cancel') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">Batalkan</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><i class="fas fa-arrow-up-from-bracket"></i> Unggah Excel</div>
            <div class="card-body">
                <form action="<?= base_url('verifikasi-ipk/upload') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="excel" class="form-label">File Excel (kolom <code>nim</code> dan <code>ipk</code>)</label>
                        <input type="file" class="form-control" id="excel" name="excel" accept=".xlsx" required>
                        <div class="form-text">
                            Gunakan template Graduation (nim, nama, tgl_keluar, ipk, nilai_skripsi).
                            Hanya kolom <code>nim</code> dan <code>ipk</code> yang dibaca. IPK Excel dianggap benar
                            dan akan menimpa IPK semester terakhir yang <strong>aktif</strong> di Neo Feeder.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Unggah &amp; Mulai Verifikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>
