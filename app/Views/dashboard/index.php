    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h3 class="m-0">Dashboard</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-6 mb-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 class="text-truncate"><?= esc($username ?? session('auth.username')) ?></h3>
                            <p>Pengguna Aktif</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6 mb-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>NeoFeeder</h3>
                            <p>Service</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-database"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6 mb-4">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>Session</h3>
                            <p>Status</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6 mb-4">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>BASE</h3>
                            <p>v0.1.0</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cube"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
