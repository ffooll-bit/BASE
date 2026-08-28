<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">PISN Graduation — Unggah Data</h3>
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
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <i class="fas fa-times-circle"></i> <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($canResume): ?>
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <span><i class="fas fa-rotate"></i> Ada sesi verifikasi yang belum selesai.</span>
                <div class="d-flex gap-2">
                    <a href="<?= base_url('graduation/resume') ?>" class="btn btn-sm btn-primary">Lanjutkan</a>
                    <form action="<?= base_url('graduation/cancel') ?>" method="post">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan sesi</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <p class="text-muted">
                    Unggah berkas Excel (<code>.xlsx</code>) berisi calon wisudawan. Kolom yang dikenali
                    (huruf kecil, baris pertama): <code>nim</code>* (wajib), <code>nama</code>,
                    <code>jenis_keluar</code>, <code>tgl_keluar</code>, <code>periode_keluar</code>,
                    <code>ipk</code>. Field <code>no_ijazah</code> otomatis diisi &quot;-&quot;.
                </p>
                <form action="<?= base_url('graduation/upload') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="excel" class="form-label">Berkas Excel</label>
                        <input type="file" class="form-control" id="excel" name="excel" accept=".xlsx" required>
                    </div>
<div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Unggah & Mulai Verifikasi
                        </button>
                        <a href="<?= base_url('graduation/template') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-download"></i> Unduh Template
                        </a>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>
