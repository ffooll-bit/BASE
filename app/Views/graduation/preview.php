<div class="app-content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h3 class="m-0">Konfirmasi Data Kelulusan</h3>
            </div>
            <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('graduation') ?>">PISN Graduation</a></li>
                        <li class="breadcrumb-item active">Konfirmasi</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<div class="app-content">
    <div class="container-fluid">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Silakan periksa data kelulusan berikut sebelum mengirim ke Neo Feeder. Klik <strong>Kirim ke Neo Feeder</strong> untuk memproses, atau <strong>Kembali</strong> untuk mengubah data.
        </div>

        <?php if (empty($students)): ?>
            <div class="alert alert-warning">Tidak ada data mahasiswa untuk dikonfirmasi.</div>
        <?php else: ?>
            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Jenis Keluar</th>
                                <th>Tanggal Keluar/Lulus</th>
                                <th>Periode Keluar</th>
                                <th>IPK</th>
                                <th>No Ijazah / No Sertifikat Profesi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $idx => $student): ?>
                                <?php $g = $student['graduation'] ?? []; ?>
                                <tr>
                                    <td><?= esc($idx + 1) ?></td>
                                    <td><?= esc($g['nim'] ?? $student['nim'] ?? '') ?></td>
                                    <td><?= esc($g['nama'] ?? $student['nama'] ?? '') ?></td>
                                    <td><?= esc($g['jenis_keluar'] ?? $student['jenis_keluar'] ?? '') ?></td>
                                    <td><?= esc($g['tgl_keluar'] ?? $student['tgl_keluar'] ?? '') ?></td>
                                    <td><?= esc($g['periode_keluar'] ?? $student['periode_keluar'] ?? '') ?></td>
                                    <td><?= esc($g['ipk'] ?? $student['ipk'] ?? '') ?></td>
                                    <td><?= esc($g['no_ijazah'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="<?= base_url('graduation/step') ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <form method="post" action="<?= base_url('graduation/finish') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin mengirim data ini ke Neo Feeder?');">
                            <i class="fas fa-paper-plane"></i> Kirim ke Neo Feeder
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>