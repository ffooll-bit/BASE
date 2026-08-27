<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">Edit Mahasiswa</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('mahasiswa') ?>">Daftar Mahasiswa</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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

        <?php if (session('message')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <?= esc(session('message')) ?>
            </div>
        <?php endif; ?>

        <?php if ($row === null): ?>
            <div class="alert alert-warning">Data tidak ditemukan.</div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <form method="post" action="<?= base_url('mahasiswa/edit/' . esc($id)) ?>">
                        <?= csrf_field() ?>
                        <?php foreach ($row as $col => $val): ?>
                            <?php if ($col === 'id_mahasiswa') {
                                continue;
                            } ?>
                            <div class="mb-2">
                                <label class="form-label"><?= esc($col) ?></label>
                                <input type="text" class="form-control" name="<?= esc($col) ?>" value="<?= esc($val) ?>">
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="<?= base_url('mahasiswa') ?>" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>
