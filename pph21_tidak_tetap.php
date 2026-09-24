<?php
session_start();
require 'config/koneksi.php';
$title = "Pajak PPh 21 - Pegawai Tidak Tetap";
require 'layout/header.php'; 
require 'layout/sidebar.php'; 

// Ambil List Pegawai yang BUKAN Pegawai Tetap (misal: Kontrak / Tidak Tetap / Harian)
$pegawai_query = mysqli_query($conn, "SELECT id_pegawai, nama, status_pegawai FROM pegawai WHERE status_pegawai != 'PEGAWAI TETAP' ORDER BY nama ASC");

// Ambil histori pajak tidak tetap
$hasil_query = mysqli_query($conn, "
    SELECT t.*, p.nama 
    FROM pph21_tidak_tetap t 
    JOIN pegawai p ON t.id_pegawai = p.id_pegawai 
    ORDER BY t.created_at DESC
");
?>

<div id="layoutSidenav_content">
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-fluid px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3 pb-3">
                        <div class="col-auto mb-3 mb-sm-0">
                            <h1 class="page-header-title text-primary font-weight-bold">
                                <i data-feather="clock" class="me-2"></i> Kalkulator PPh 21 Pegawai Tidak Tetap
                            </h1>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-fluid px-4">
            <?php if(isset($_SESSION['sukses'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['sukses']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['sukses']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- FORM INPUT KALKULATOR -->
                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white fw-bold">Form Input TER Harian</div>
                        <div class="card-body">
                            <form action="proses_hitung_tidak_tetap.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Pegawai Tidak Tetap</label>
                                    <select name="id_pegawai" class="form-select" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        <?php while($p = mysqli_fetch_assoc($pegawai_query)): ?>
                                            <option value="<?= $p['id_pegawai'] ?>"><?= htmlspecialchars($p['nama']) ?> (<?= $p['status_pegawai'] ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kode Objek Pajak</label>
                                    <input type="text" class="form-control bg-light" value="21-100-03 Pegawai Tidak Tetap / Tenaga Lepas" readonly>
                                </div>
                                <div class="row">
                                    <div class="col-md-7 mb-3">
                                        <label class="form-label">Upah Per Hari (Rp)</label>
                                        <input type="number" name="upah_harian" class="form-control" placeholder="Contoh: 300000" required>
                                    </div>
                                    <div class="col-md-5 mb-3">
                                        <label class="form-label">Jml Hari Kerja</label>
                                        <input type="number" name="jumlah_hari_kerja" class="form-control" placeholder="Contoh: 5" required>
                                    </div>
                                </div>
                                <div class="alert alert-light border small text-muted">
                                    <i data-feather="info" class="me-1" style="width: 14px; height: 14px;"></i> Sesuai PMK 168/2023, upah harian ≤ Rp450.000 dikenakan TER 0%. Di atas Rp450.000 s.d Rp2,5 Juta dikenakan TER 0,5%.
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="hitung_tidak_tetap" class="btn btn-success fw-bold">
                                        <i data-feather="calculator" class="me-1"></i> Hitung PPh 21 Harian
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TABEL HASIL (DATATABLES) -->
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div><i data-feather="table" class="me-1"></i> Riwayat Pemotongan (21-100-03)</div>
                            <button class="btn btn-sm btn-outline-success"><i data-feather="printer"></i> Cetak</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>NAMA</th>
                                            <th>TGL PROSES</th>
                                            <th>HARI</th>
                                            <th>TOTAL BRUTO</th>
                                            <th>TARIF</th>
                                            <th>PPH 21</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($hasil_query)) : ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($row['nama']); ?></td>
                                            <td><?= date('d/m/Y', strtotime($row['tanggal_proses'])); ?></td>
                                            <td class="text-center"><?= $row['jumlah_hari_kerja']; ?></td>
                                            <td class="text-primary">Rp <?= number_format($row['penghasilan_bruto'], 0, ',', '.'); ?></td>
                                            <td class="text-center"><span class="badge bg-secondary"><?= $row['tarif_ter_harian']; ?>%</span></td>
                                            <td class="text-danger fw-bold">Rp <?= number_format($row['pph21_dipotong'], 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php require 'layout/footer.php'; ?>