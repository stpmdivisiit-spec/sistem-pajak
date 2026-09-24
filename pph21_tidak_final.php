<?php
session_start();
require 'config/koneksi.php';

if(!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$title = "Kalkulator PPh 21 - Tidak Final";
require 'layout/header.php'; 
require 'layout/sidebar.php'; 

$role_login = $_SESSION['role'];
$npwp_login = $_SESSION['npwp']; // Gunakan NPWP karena bukan pegawai reguler

// FILTER QUERY
if ($role_login == 'admin') {
    $hasil_query = mysqli_query($conn, "SELECT * FROM pph21_tidak_final ORDER BY created_at DESC");
} else {
    // User hanya melihat data yang NPWP-nya sama dengan NPWP mereka saat login
    $hasil_query = mysqli_query($conn, "SELECT * FROM pph21_tidak_final WHERE npwp = '$npwp_login' ORDER BY created_at DESC");
}

// Ambil data NPWP untuk dropdown (Hanya butuh di sisi Admin, tapi biarkan saja)
$pegawai_query = mysqli_query($conn, "SELECT npwp, nama FROM pegawai WHERE npwp IS NOT NULL AND npwp != '' ORDER BY npwp ASC");
?>

<!-- Tambahkan CSS Select2 di bagian atas halaman -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div id="layoutSidenav_content">
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-fluid px-4">
                <div class="page-header-content">
                    <div class="row align-items-center pt-3 pb-3">
                        <div class="col-auto">
                            <h1 class="page-header-title text-primary font-weight-bold">
                                <i data-feather="briefcase" class="me-2"></i> PPh 21 Tidak Final
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
                <!-- FORM INPUT -->
                 <?php if($role_login == 'admin') : ?>
                <div class="col-lg-4">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-primary text-white fw-bold border-0">Hitung PPh 21 Tidak Final</div>
                        <div class="card-body bg-light">
                            <form action="proses_hitung_tidak_final.php" method="POST">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Pilih NPWP Penerima</label>
                                    <select name="npwp" id="select_npwp" class="form-select select2" required>
                                        <option value="">-- Cari NPWP --</option>
                                        <?php while($p = mysqli_fetch_assoc($pegawai_query)): ?>
                                            <!-- Kita simpan nama di atribut data-nama agar mudah diambil oleh JavaScript -->
                                            <option value="<?= htmlspecialchars($p['npwp']) ?>" data-nama="<?= htmlspecialchars($p['nama']) ?>">
                                                <?= htmlspecialchars($p['npwp']) ?> - <?= htmlspecialchars($p['nama']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted">Nama Penerima / Vendor (Otomatis)</label>
                                    <!-- Field ini readonly dan akan diisi oleh JavaScript -->
                                    <input type="text" name="nama_penerima" id="nama_penerima_readonly" class="form-control" readonly required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted">Kode Objek Pajak</label>
                                    <select name="kode_objek_pajak" id="kode_objek" class="form-select" required onchange="cekFormHari()">
                                        <option value="">-- Pilih Kode --</option>
                                        <option value="21-100-03">21-100-03 Pegawai Tidak Tetap</option>
                                        <option value="21-100-04">21-100-04 Distributor Pemasaran Berjenjang</option>
                                        <option value="21-100-05">21-100-05 Agen Asuransi</option>
                                        <option value="21-100-06">21-100-06 Penjaja Barang Dagangan</option>
                                        <option value="21-100-07">21-100-07 Tenaga Ahli</option>
                                        <option value="21-100-08">21-100-08 Seniman / Olahragawan</option>
                                    </select>
                                </div>
                                
                                <!-- Akan tampil hanya jika 21-100-03 dipilih -->
                                <div class="mb-3" id="form_hari" style="display: none;">
                                    <label class="form-label text-danger fw-bold">Jumlah Hari Kerja</label>
                                    <input type="number" name="hari_kerja" class="form-control" value="1">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label text-muted">Total Penghasilan Bruto (Rp)</label>
                                    <input type="number" name="penghasilan_bruto" class="form-control" required>
                                </div>
                                <button type="submit" name="hitung_tidak_final" class="btn btn-primary w-100 fw-bold py-2">Hitung Pajak</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <!-- TABEL HASIL (Sama seperti sebelumnya) -->
                <div class="<?= ($role_login == 'admin') ? 'col-lg-8' : 'col-lg-12' ?>">
                    <div class="card mb-4 shadow-sm border-0">
                        <div class="card-header bg-white border-bottom"><i data-feather="table" class="me-1 text-primary"></i> Data Pemotongan Tidak Final</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" id="dataTable" width="100%">
                                    <thead class="bg-light text-muted small">
                                        <tr>
                                            <th>NAMA</th>
                                            <th>KODE OBJEK</th>
                                            <th>BRUTO</th>
                                            <th>DASAR PENGENAAN (DPP)</th>
                                            <th>SKEMA</th>
                                            <th>PPH 21</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($hasil_query)) : ?>
                                        <tr>
                                            <td class="fw-bold text-dark">
                                                <?= htmlspecialchars($row['nama_penerima']); ?>
                                                <div class="small text-muted fw-normal">NPWP: <?= htmlspecialchars($row['npwp']); ?></div>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= $row['kode_objek_pajak']; ?></span></td>
                                            <td>Rp <?= number_format($row['penghasilan_bruto'], 0, ',', '.'); ?></td>
                                            <td class="text-primary">Rp <?= number_format($row['dpp_pajak'], 0, ',', '.'); ?></td>
                                            <td class="small text-muted"><?= $row['tarif_efektif']; ?></td>
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

<!-- Tambahkan jQuery (dibutuhkan oleh Select2) dan Script JS di paling bawah sebelum tag penutup </body> (idealnya di footer.php, tapi untuk halaman spesifik bisa di sini) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi Select2 dengan tema Bootstrap 5
        $('.select2').select2({
            theme: "bootstrap-5",
            width: '100%',
            placeholder: "-- Cari NPWP atau Nama --"
        });

        // Event Trigger saat NPWP dipilih
        $('#select_npwp').on('change', function() {
            // Ambil data-nama dari option yang dipilih
            var namaPegawai = $(this).find(':selected').data('nama');
            
            // Masukkan ke input text readonly
            if(namaPegawai) {
                $('#nama_penerima_readonly').val(namaPegawai);
            } else {
                $('#nama_penerima_readonly').val('');
            }
        });
    });

    // Fungsi untuk menyembunyikan/menampilkan form hari kerja
    function cekFormHari() {
        var kode = document.getElementById("kode_objek").value;
        var formHari = document.getElementById("form_hari");
        if(kode === "21-100-03") {
            formHari.style.display = "block";
        } else {
            formHari.style.display = "none";
        }
    }
</script>