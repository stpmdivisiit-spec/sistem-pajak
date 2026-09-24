<?php
session_start();
require 'config/koneksi.php';

// Cek autentikasi
if(!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$title = "Pajak PPh 21 - Pegawai Tetap";
require 'layout/header.php'; 
require 'layout/sidebar.php'; 

$periode_bulan = date('m');
$periode_tahun = date('Y');

$role_login = $_SESSION['role'];
$id_login   = $_SESSION['id_pegawai'];

// KONDISI FILTER QUERY: Admin lihat semua, User lihat miliknya sendiri
if ($role_login == 'admin') {
    $query_hasil = "
        SELECT p.nama, p.npwp, h.* 
        FROM pph21_bulanan h 
        JOIN pegawai p ON h.id_pegawai = p.id_pegawai
        WHERE h.periode_bulan = '$periode_bulan' AND h.periode_tahun = '$periode_tahun'
        ORDER BY p.nama ASC
    ";
} else {
    $query_hasil = "
        SELECT p.nama, p.npwp, h.* 
        FROM pph21_bulanan h 
        JOIN pegawai p ON h.id_pegawai = p.id_pegawai
        WHERE h.periode_bulan = '$periode_bulan' AND h.periode_tahun = '$periode_tahun' AND h.id_pegawai = '$id_login'
    ";
}

$hasil_pajak = mysqli_query($conn, $query_hasil);
?>

<div id="layoutSidenav_content">
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-fluid px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3 pb-3">
                        <div class="col-auto mb-3 mb-sm-0">
                            <h1 class="page-header-title text-primary font-weight-bold">
                                <div class="page-header-icon"><i data-feather="calculator"></i></div>
                                Kalkulator PPh 21 Bulanan (21-100-01)
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid px-4">
            <!-- Alert Notifikasi -->
            <?php if(isset($_SESSION['sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['sukses']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['sukses']); ?>
            <?php endif; ?>

            <!-- Panel Tombol Hitung Otomatis (Mirip Gambar) -->
<!-- Sembunyikan Panel Proses Hitung Massal dari User Biasa -->
            <?php if($role_login == 'admin') : ?>
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body bg-light rounded d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-1 text-dark">Proses Hitung Massal</h5>
                        <p class="text-muted small mb-0">Klik tombol di samping untuk menghitung PPh 21 skema TER (Gross) seluruh Pegawai Tetap pada periode berjalan.</p>
                    </div>
                    <form action="proses_hitung_pph21.php" method="POST">
                        <button type="submit" name="hitung_pph21" class="btn btn-warning fw-bold text-dark px-4 py-2" onclick="return confirm('Mulai proses perhitungan pajak bulan ini? Data yang sudah ada di bulan ini akan diperbarui.');">
                            <i data-feather="cpu" class="me-2"></i> Hitung PPh 21 Otomatis
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Tabel Hasil DataTables -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div><i data-feather="table" class="me-1"></i> Data Pemotongan PPh 21 - Periode: <?= date('F Y'); ?></div>
                    <button class="btn btn-sm btn-success"><i data-feather="download" class="me-1"></i> Export e-Bupot</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- ID = dataTable untuk inisiasi plugin DataTables bawaan SB Admin Pro -->
                        <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th>NO</th>
                                    <th>NAMA</th>
                                    <th>NPWP</th>
                                    <th>PTKP (TER)</th>
                                    <th>GAJI BRUTO (DPP)</th>
                                    <th>TARIF TER</th>
                                    <th>PPH 21 DIPOTONG</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                $total_pajak = 0;
                                while($row = mysqli_fetch_assoc($hasil_pajak)) : 
                                    $total_pajak += $row['pph21_dipotong'];
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['npwp']); ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status_ptkp']); ?> (TER <?= $row['kategori_ter']; ?>)</span></td>
                                    <td>Rp <?= number_format($row['dpp_pajak'], 0, ',', '.'); ?></td>
                                    <td class="text-center fw-bold"><?= htmlspecialchars($row['tarif_ter_persen']); ?> %</td>
                                    <td class="text-danger fw-bold">Rp <?= number_format($row['pph21_dipotong'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="6" class="text-end">TOTAL PAJAK TERHUTANG (BULAN INI):</th>
                                    <th class="text-danger fw-bold fs-5">Rp <?= number_format($total_pajak, 0, ',', '.'); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php require 'layout/footer.php'; ?>