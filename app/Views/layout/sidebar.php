    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= base_url('dashboard') ?>" class="brand-link">
                <span class="brand-text fw-light">BASE</span>
            </a>
        </div>

        <div class="sidebar-wrapper">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <span class="img-circle elevation-2 bg-info d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= esc(session('auth.username')) ?></a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                    <li class="nav-item">
                        <a href="<?= base_url('dashboard') ?>" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="app-main">
