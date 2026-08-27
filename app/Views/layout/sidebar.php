    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= base_url('dashboard') ?>" class="brand-link">
                <span class="brand-text fw-light">BASE</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <!-- Sidebar Menu -->
            <?php $currentUrl = (string) current_url();
            $currentRoute = basename(parse_url($currentUrl, PHP_URL_PATH)); ?>
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                    <li class="nav-item">
                        <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-gauge-high"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('profil-pt') ?>" class="nav-link <?= $currentRoute === 'profil-pt' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-university"></i>
                            <p>Profil Perguruan Tinggi</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('mahasiswa') ?>" class="nav-link <?= $currentRoute === 'mahasiswa' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-user-graduate"></i>
                            <p>Mahasiswa</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('aktivitas-kuliah') ?>" class="nav-link <?= $currentRoute === 'aktivitas-kuliah' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-chalkboard-teacher"></i>
                            <p>Aktivitas Kuliah</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('mahasiswa-lulus-do') ?>" class="nav-link <?= $currentRoute === 'mahasiswa-lulus-do' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-user-check"></i>
                            <p>Mahasiswa Lulus / DO</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
