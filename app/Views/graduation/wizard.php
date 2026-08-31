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
                            <?php if (in_array($key, $uuidKeys, true) || (is_string($value) && preg_match($uuidRegex, $value))) {
                                continue;
                            } ?>
                            <tr><th><?= esc($key) ?></th><td><?= esc($value) ?></td></tr>
                        <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="identity_ok" name="identity_ok" value="1" <?= (!empty($student['identity_ok']) || !empty($identityOk)) ? 'checked' : '' ?>>
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
                        <?php if ($lastAcademic !== null): ?>
                            <?php
                            // ENH-006: single-source "last IPK" synced two-ways between the
                            // last academic row and the step-5 graduation IPK input.
                            $lastIPK = trim((string) ($lastAcademic['ipk'] ?? ''));
                            $sValue  = 'Aktif';
                            $activeCode = null;
                            foreach (($statusOptions ?? []) as $code => $label) {
                                if (trim((string) $label) === $sValue) {
                                    $activeCode = (string) $code;
                                    break;
                                }
                            }
                            ?>
                            <?php if (! $lastIsActive): ?>
                                <div class="alert alert-warning py-2">
                                    Semester terakhir (<strong><?= esc($lastAcademic['id_semester']) ?></strong>) sudah tidak aktif,
                                    sehingga IPK terakhirnya tidak dapat disinkronkan otomatis.
                                </div>
                            <?php elseif ($activeCode !== null && $lastIPK !== '' && trim((string) $excelIpk) !== $lastIPK): ?>
                                <div class="alert alert-warning py-2 d-flex align-items-center justify-content-between flex-wrap gap-2" id="ipk-mismatch">
                                    <span>IPK Excel (<strong><?= esc($excelIpk) ?></strong>) berbeda dengan IPK semester terakhir (<strong><?= esc($lastIPK) ?></strong>).</span>
                                    <button type="button" class="btn btn-sm btn-warning" id="auto-update-ipk">
                                        Auto-update IPK &amp; status ke Aktif
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="table-responsive mb-2">
                            <?php
                            $editableCols = ['id_status_mahasiswa', 'ips', 'ipk'];
                        $acCols       = [];
                        foreach (array_keys($academic[0]) as $k) {
                            $sample = $academic[0][$k] ?? '';
                            if (in_array($k, $uuidKeys, true) || (is_string($sample) && preg_match($uuidRegex, $sample))) {
                                continue;
                            }
                            $acCols[] = $k;
                        }
                        ?>
                            <table class="table table-sm table-bordered table-striped">
                                <thead><tr><?php foreach ($acCols as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
                                <tbody>
                                    <?php foreach ($academic as $row): ?>
                                        <?php
                                        $isActive = in_array((string) $row['id_semester'], (array) ($activeSemesterIds ?? []), true);
                                        $disabled = $isActive ? '' : ' disabled';
                                        $isLastRow = ($lastAcademic !== null && (string) $row['id_semester'] === (string) $lastAcademic['id_semester']);
                                        ?>
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
                                                        <?php $lastSelAttrs = ($isLastRow && ! $disabled) ? ' id="last-status"' : ''; ?>
                                                        <select class="form-select form-select-sm"
                                                                name="academics[<?= esc($row['id_semester']) ?>][id_status_mahasiswa]"<?= $lastSelAttrs ?><?= $disabled ?>><?= $optionsHtml ?></select>
                                                    <?php elseif (in_array($col, $editableCols, true)): ?>
                                                        <?php $lastIpkAttrs = ($isLastRow && $col === 'ipk' && ! $disabled) ? ' id="last-ipk"' : ''; ?>
                                                        <input type="text" class="form-control form-control-sm"
                                                               name="academics[<?= esc($row['id_semester']) ?>][<?= esc($col) ?>]"
                                                               value="<?= esc($student['academics'][$row['id_semester']][$col] ?? $row[$col] ?? '') ?>"<?= $lastIpkAttrs ?><?= $disabled ?>>
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
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="academic_ok" name="academic_ok" value="1" <?= (!empty($student['academic_ok']) || !empty($academicOk)) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="academic_ok">IPK semester terakhir sesuai &amp; status Aktif</label>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-clipboard-check"></i> 3. Kelengkapan Transkrip</div>
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

                        <?php if (empty($completeness['thesis'])): ?>
                            <div class="text-muted small mb-2">
                                <i class="fas fa-circle-question"></i> MK skripsi/tugas akhir tidak ditemukan di transkrip.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-2">
                                    <thead class="table-light">
                                        <tr>
                                            <th>MK</th>
                                            <th>Kode</th>
                                            <th>Semester</th>
                                            <th>Nilai</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($completeness['thesis'] as $t): ?>
                                        <?php
                                        $excelGrade = trim((string) ($student['nilai_skripsi'] ?? ''));
                                        $targetSmt  = $targetThesisSemester ?? null;
                                        $willFill   = ! $t['hasGrade']
                                            && $excelGrade !== ''
                                            && (string) ($t['semester'] ?? '') === (string) $targetSmt;
                                        ?>
                                        <tr>
                                            <td><?= esc($t['nama']) ?></td>
                                            <td><?= esc($t['kode']) ?></td>
                                            <td><?= esc($t['semester']) ?></td>
                                            <td>
                                                <?php if ($t['nilai']): ?>
                                                    <?= esc($t['nilai']) ?>
                                                <?php elseif ($willFill): ?>
                                                    <?= esc($excelGrade) ?> <span class="text-success small">(akan diisi)</span>
                                                <?php else: ?>
                                                    <span class="text-danger">Belum ada nilai</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (! $t['hasGrade']): ?>
                                                    <span class="badge text-bg-danger">Tanpa nilai</span>
                                                <?php elseif ($t['choosed']): ?>
                                                    <span class="badge text-bg-success">Nilai ada · masuk transkrip</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-warning">Nilai ada · belum masuk transkrip</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <div class="text-muted small">Jumlah nilai terload: <?= esc(count($transcript)) ?></div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="transcript_ok" name="transcript_ok" value="1" <?= (!empty($student['transcript_ok']) || !empty($transcriptOk)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="transcript_ok">MK skripsi/tugas akhir bernilai &amp; masuk transkrip</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="fas fa-certificate"></i> 4. Eligibilitas PISN</div>
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
                <div class="card-header"><i class="fas fa-graduation-cap"></i> 5. Input Kelulusan</div>
                <div class="card-body">
                    <?php
                        $g = $student['graduation']
                        ?? ['nim' => $student['nim'], 'nama' => $student['nama'], 'jenis_keluar' => $student['jenis_keluar'], 'tgl_keluar' => $student['tgl_keluar'], 'periode_keluar' => $student['periode_keluar'], 'ipk' => $student['ipk'], 'no_ijazah' => '-'];
                        ?>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="form-label">NPM / NIM</label><input class="form-control" name="nim" value="<?= esc($g['nim']) ?>" required></div>
                        <div class="col-md-8"><label class="form-label">Nama</label><input class="form-control" name="nama" value="<?= esc($g['nama']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Jenis Keluar</label>
                            <?php
                            $jkSel = (string) $g['jenis_keluar'];
                        $jkSel = (isset($jenisKeluarOptions[$jkSel]))
                            ? $jkSel
                            : (array_search($jkSel, $jenisKeluarOptions, true) !== false
                                ? (string) array_search($jkSel, $jenisKeluarOptions, true)
                                : '');
                        $jkHtml = '<option value="">—</option>';
                        foreach (($jenisKeluarOptions ?? []) as $code => $label) {
                            $sel = ($jkSel === (string) $code) ? ' selected' : '';
                            $jkHtml .= '<option value="' . esc($code) . '"' . $sel . '>' . esc($label) . '</option>';
                        }
                        ?>
                            <select class="form-select" name="jenis_keluar" required><?= $jkHtml ?></select>
                        </div>
                        <div class="col-md-4"><label class="form-label">Tanggal Keluar/Lulus</label><input type="date" class="form-control" name="tgl_keluar" value="<?= esc($g['tgl_keluar']) ?>" required></div>
                        <div class="col-md-4"><label class="form-label">Periode Keluar</label>
                            <?php
                        $smSel = (string) $g['periode_keluar'];
                        $smHtml = '<option value="">—</option>';
                        foreach (($semesterOptions ?? []) as $code => $label) {
                            $sel = ($smSel === (string) $code) ? ' selected' : '';
                            $smHtml .= '<option value="' . esc($code) . '"' . $sel . '>' . esc($label) . '</option>';
                        }
                        ?>
                            <select class="form-select" name="periode_keluar" required><?= $smHtml ?></select>
                        </div>
                        <div class="col-md-4"><label class="form-label">IPK</label><input class="form-control" name="ipk" id="graduation_ipk" value="<?= esc($g['ipk']) ?>" required></div>
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
        <?php if ($lastAcademic !== null): ?>
        <script>
        (function () {
            var lastIpk  = document.getElementById('last-ipk');
            var lastStat = document.getElementById('last-status');
            var gradIpk  = document.getElementById('graduation_ipk');
            var acadOk   = document.getElementById('academic_ok');
            if (!lastIpk || !gradIpk) { return; }

            // ENH-008: real-time auto-check for step 2. The checkbox reflects
            // (last-row IPK === Excel IPK) AND (last-row status === Aktif).
            var excelIpk   = <?= json_encode(trim((string) $excelIpk)) ?>;
            var activeCode = <?= json_encode($activeCode) ?>;
            function recomputeAcademicOk() {
                if (!acadOk) { return; }
                var cond = lastIpk.value === excelIpk
                    && lastStat !== null && lastStat.value === activeCode;
                if (cond) { acadOk.checked = true; }
            }

            function sync(a, b) {
                if (a.value !== b.value) { b.value = a.value; }
            }
            lastIpk.addEventListener('input', function () { sync(lastIpk, gradIpk); recomputeAcademicOk(); });
            gradIpk.addEventListener('input', function () { sync(gradIpk, lastIpk); recomputeAcademicOk(); });

            var btn = document.getElementById('auto-update-ipk');
            if (btn && lastStat && activeCode) {
                btn.addEventListener('click', function () {
                    lastIpk.value = <?= json_encode(trim((string) $excelIpk)) ?>;
                    lastStat.value = activeCode;
                    sync(lastIpk, gradIpk);
                    var banner = document.getElementById('ipk-mismatch');
                    if (banner) { banner.remove(); }
                    recomputeAcademicOk();
                });
            }

            recomputeAcademicOk();
        })();
        </script>
        <?php endif; ?>
    </div>
</div>
