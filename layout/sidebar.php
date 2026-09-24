<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        <nav class="sidenav shadow-right sidenav-light">
            <div class="sidenav-menu">
                <div class="nav accordion" id="accordionSidenav">
                    
                    <!-- Menu Data Pegawai -->
                    <!-- Menggunakan request URI untuk membuat menu active secara dinamis (opsional) -->
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'data_pegawai.php') ? 'active bg-primary text-white mx-3 rounded mt-3' : 'mt-3'; ?>" href="data_pegawai.php">
                        <div class="nav-link-icon <?= (basename($_SERVER['PHP_SELF']) == 'data_pegawai.php') ? 'text-white' : ''; ?>">
                            <i data-feather="users"></i>
                        </div>
                        Data Pegawai
                    </a>

                    <!-- Divider -->
                    <div class="sidenav-menu-heading">Modul Perpajakan</div>

                    <!-- Menu Pajak PPh 21 -->
                    <a class="nav-link collapsed" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#collapsePajak" aria-expanded="false" aria-controls="collapsePajak">
                        <div class="nav-link-icon"><i data-feather="dollar-sign"></i></div>
                        Pajak PPh 21
                        <div class="sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapsePajak" data-bs-parent="#accordionSidenav">
                        <nav class="sidenav-menu-nested nav accordion" id="accordionSidenavPages">
                            
                            <!-- Sub Kategori: Pemotongan Bulanan / Tidak Final -->
                            <div class="sidenav-menu-heading text-xs mt-2 mb-1">Jenis Potongan Bulanan</div>
                            <a class="nav-link" href="pph21_tetap.php">
                                • Pegawai Tetap (21-100-01)
                            </a>
                            <a class="nav-link" href="pph21_tidak_tetap.php">
                                • Pegawai Tidak Tetap (21-100-03)
                            </a>
                            <a class="nav-link" href="#">
                                • PPh 21 PNS (21-100-02)
                            </a>

                            <!-- Sub Kategori: Pemotongan Final -->
                            <div class="sidenav-menu-heading text-xs mt-3 mb-1">Pemotongan Final</div>
                            <a class="nav-link" href="pph21_final.php">
                                • Pesangon & Pensiun (21-401-01)
                            </a>
                            <a class="nav-link" href="pph21_tidak_final.php">
                                • PPh 21 Tidak Final (03 - 08)
                            </a>

                        </nav>
                    </div>

                </div>
            </div>
            
            <!-- Footer Sidebar -->
<!-- Footer Sidebar -->
            <div class="sidenav-footer">
                <div class="sidenav-footer-content">
                    <div class="sidenav-footer-subtitle">Masuk sebagai:</div>
                    <!-- Tampilkan Nama dan Role Dinamis dari Session -->
                    <div class="sidenav-footer-title fw-bold text-primary">
                        <?= isset($_SESSION['nama']) ? htmlspecialchars($_SESSION['nama']) : 'Tamu'; ?>
                    </div>
                    <div class="small text-muted mb-2 text-uppercase" style="font-size: 0.70rem;">
                        [ <?= isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; ?> ]
                    </div>
                    
                    <!-- Tombol Logout -->
                    <a href="logout.php" class="btn btn-sm btn-danger w-100 fw-bold shadow-sm">
                        <i data-feather="log-out" class="me-1"></i> Keluar
                    </a>
                </div>
            </div>
        </nav>
    </div>