<?php
session_start();
require 'config/koneksi.php';

// Simulasi Session (Nanti didapat dari proses login)
// $_SESSION['role'] = 'admin'; // atau 'user'
// $_SESSION['username'] = 'admin_stpm';

$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Mengambil data pegawai dari database
$query = "SELECT * FROM pegawai ORDER BY id_pegawai ASC";
$result = mysqli_query($conn, $query);

$title = "Modul Data Pegawai";
require 'layout/header.php'; 
require 'layout/sidebar.php'; // Pastikan sidebar.php memuat menu YASPA, Data Pegawai, dan PPh 21
?>

<div id="layoutSidenav_content">
    <main>
        <!-- Header Halaman -->
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-fluid px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3 pb-3">
                        <div class="col-auto mb-3 mb-sm-0">
                            <h1 class="page-header-title text-primary font-weight-bold">
                                Modul Data Pegawai
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mb-3 mb-sm-0">
                            <button class="btn btn-sm btn-light text-success fw-500">
                                <i class="me-1" data-feather="check-circle"></i> API Connected
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Konten Utama -->
        <div class="container-fluid px-4">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i data-feather="database" class="me-1"></i> Database Pegawai</div>
                    <!-- Tombol Aksi Global -->
                    <div>
                        <button class="btn btn-sm btn-primary"><i data-feather="layers" class="me-1"></i> Hitung Semua Status</button>
                        <button class="btn btn-sm btn-success"><i data-feather="calculator" class="me-1"></i> Hitung TER</button>
                        <button class="btn btn-sm btn-warning text-dark"><i data-feather="file-text" class="me-1"></i> Hitung PPh (21-100-03)</button>
                        <a href="data_pegawai.php" class="btn btn-sm btn-outline-primary"><i data-feather="refresh-cw" class="me-1"></i> Refresh</a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Tombol Export & Pencarian -->
                    <div class="d-flex justify-content-between mb-3">
                        <div>
                            <button class="btn btn-sm btn-secondary">Copy</button>
                            <button class="btn btn-sm btn-success">Excel</button>
                            <button class="btn btn-sm btn-danger">PDF</button>
                            <button class="btn btn-sm btn-dark">Print</button>
                        </div>
                        <?php if($role == 'admin') : ?>
                            <!-- Tombol Tambah Data hanya untuk Admin -->
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                                + Tambah Pegawai
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Tabel Data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dataTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>NO</th>
                                    <th>NAMA</th>
                                    <th>POSISI</th>
                                    <th>STATUS</th>
                                    <th>TK/K</th>
                                    <th>GOL</th>
                                    <th>ID TKU</th>
                                    <th>UNIT</th>
                                    <th>NPWP</th>
                                    <th>GAJI BRUTO</th>
                                    <?php if($role == 'admin') : ?>
                                        <th>AKSI</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                while($row = mysqli_fetch_assoc($result)) : 
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['posisi']); ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_pegawai']); ?></span></td>
                                    <td><?= htmlspecialchars($row['ptkp']); ?></td>
                                    <td><?= htmlspecialchars($row['golongan'] ?: '-'); ?></td>
                                    <td><?= htmlspecialchars($row['id_tku']); ?></td>
                                    <td><?= htmlspecialchars($row['unit']); ?></td>
                                    <td><?= htmlspecialchars($row['npwp']); ?></td>
                                    <td class="text-primary fw-bold">Rp <?= number_format($row['gaji_bruto'], 0, ',', '.'); ?></td>
                                    
                                    <?php if($role == 'admin') : ?>
                                    <!-- Aksi Edit & Hapus hanya tampil jika role = admin -->
                                    <td>
                                        <a href="edit_pegawai.php?id=<?= $row['id_pegawai']; ?>" class="btn btn-warning btn-sm mb-1" title="Edit">
                                            <i data-feather="edit-2"></i>
                                        </a>
                                        <a href="proses_hapus.php?id=<?= $row['id_pegawai']; ?>" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Yakin ingin menghapus data ini?');" title="Hapus">
                                            <i data-feather="trash-2"></i>
                                        </a>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php require 'layout/footer.php'; ?>