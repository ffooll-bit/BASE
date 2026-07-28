    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= base_url('dashboard') ?>" class="brand-link">
                <span class="brand-text fw-light">BASE</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <!-- Sidebar Menu -->
            <?php $currentUri = trim(service('uri')->getPath(), '/'); ?>
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                    <li class="nav-item">
                        <a href="<?= base_url('dashboard') ?>" class="nav-link <?= $currentUri === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-gauge-high"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= base_url('profil-pt') ?>" class="nav-link <?= $currentUri === 'profil-pt' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-university"></i>
                            <p>Profil Perguruan Tinggi</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
