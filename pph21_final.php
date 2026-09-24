<?php
session_start();
require 'config/koneksi.php';
$title = "Pajak PPh 21 Final - Pesangon/Pensiun";
require 'layout/header.php'; 
require 'layout/sidebar.php'; 

// Ambil List Pegawai untuk Dropdown Form
$pegawai_query = mysqli_query($conn, "SELECT id_pegawai, nama FROM pegawai ORDER BY nama ASC");

// Ambil histori pajak final
$hasil_query = mysqli_query($conn, "
    SELECT f.*, p.nama, p.npwp 
    FROM pph21_final f 
    JOIN pegawai p ON f.id_pegawai = p.id_pegawai 
    ORDER BY f.created_at DESC
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
                                Kalkulator PPh 21 Final (Pesangon & Pensiun)
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
                        <div class="card-header bg-warning text-dark fw-bold">Form Perhitungan PPh 21 Final</div>
                        <div class="card-body">
                            <form action="proses_hitung_final.php" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Pegawai</label>
                                    <select name="id_pegawai" class="form-select" required>
                                        <option value="">-- Pilih Pegawai --</option>
                                        <?php while($p = mysqli_fetch_assoc($pegawai_query)): ?>
                                            <option value="<?= $p['id_pegawai'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kode Objek Pajak</label>
                                    <select name="kode_objek_pajak" class="form-select" required>
                                        <option value="21-401-01">21-401-01 Uang Pesangon yang Dibayarkan Sekaligus</option>
                                        <option value="21-401-02">21-401-02 Uang Manfaat Pensiun, THT/JHT Dibayar Sekaligus</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Penghasilan Bruto (Rp)</label>
                                    <input type="number" name="penghasilan_bruto" class="form-control" placeholder="0" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Akumulasi Penghasilan Sebelumnya (Jika Ada)</label>
                                    <input type="number" name="akumulasi_sebelumnya" class="form-control" value="0">
                                    <small class="text-muted">Centang jika ada transaksi dalam kurun waktu 2 tahun terakhir.</small>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="hitung_final" class="btn btn-primary fw-bold">
                                        <i data-feather="calculator" class="me-1"></i> Hitung Pajak Final
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
                            <div><i data-feather="table" class="me-1"></i> Riwayat Pemotongan PPh 21 Final</div>
                            <button class="btn btn-sm btn-success"><i data-feather="download"></i> Excel</button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>NAMA</th>
                                            <th>OBJEK PAJAK</th>
                                            <th>TGL BAYAR</th>
                                            <th>BRUTO PESANGON</th>
                                            <th>PPH 21 DIPOTONG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($hasil_query)) : ?>
                                        <tr>
                                            <td class="fw-bold"><?= htmlspecialchars($row['nama']); ?></td>
                                            <td><span class="badge bg-secondary"><?= $row['kode_objek_pajak']; ?></span></td>
                                            <td><?= date('d-M-Y', strtotime($row['tanggal_bayar'])); ?></td>
                                            <td>Rp <?= number_format($row['penghasilan_bruto'], 0, ',', '.'); ?></td>
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