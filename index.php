<?php 
session_start();
require 'config/koneksi.php';

// Proteksi Halaman: Jika belum login, tendang ke login.php
if(!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$title = "Dashboard YASPA";
require 'layout/header.php'; 
require 'layout/sidebar.php'; 

$tahun_ini = date('Y');
$role_login = $_SESSION['role'];
$id_login   = $_SESSION['id_pegawai'];

// KONDISI FILTER BERDASARKAN ROLE
// Jika admin, filter ID kosong (semua data). Jika user, filter WHERE id_pegawai = miliknya.
$filter_pegawai = ($role_login == 'admin') ? "" : "WHERE id_pegawai = '$id_login'";
$filter_pph_bln = ($role_login == 'admin') ? "WHERE periode_tahun = '$tahun_ini'" : "WHERE periode_tahun = '$tahun_ini' AND id_pegawai = '$id_login'";
$filter_pph_fnl = ($role_login == 'admin') ? "WHERE YEAR(tanggal_bayar) = '$tahun_ini'" : "WHERE YEAR(tanggal_bayar) = '$tahun_ini' AND id_pegawai = '$id_login'";

// 1. STATISTIK KARTU RINGKASAN (TOTAL)
// Total Pegawai (Admin = Semua, User = Hanya 1)
$q_pegawai = mysqli_query($conn, "SELECT COUNT(*) as total FROM pegawai $filter_pegawai");
$r_pegawai = mysqli_fetch_assoc($q_pegawai)['total'];

// Total PPh 21 Bulanan (Pegawai Tetap)
$q_pph_tetap = mysqli_query($conn, "SELECT SUM(pph21_dipotong) as total FROM pph21_bulanan $filter_pph_bln");
$total_pph_tetap = mysqli_fetch_assoc($q_pph_tetap)['total'] ?: 0;

// Total PPh 21 Final (Pesangon/Pensiun)
$q_pph_final = mysqli_query($conn, "SELECT SUM(pph21_dipotong) as total FROM pph21_final $filter_pph_fnl");
$total_pph_final = mysqli_fetch_assoc($q_pph_final)['total'] ?: 0;

// Total PPh 21 Tidak Final
// Khusus Tidak Final, karena vendor/orang luar mungkin tidak punya id_pegawai di tabel pegawai, kita filter NPWP-nya.
$npwp_login = $_SESSION['npwp'];
$filter_pph_tf = ($role_login == 'admin') ? "WHERE YEAR(tanggal_potong) = '$tahun_ini'" : "WHERE YEAR(tanggal_potong) = '$tahun_ini' AND npwp = '$npwp_login'";
$q_pph_tidak_final = mysqli_query($conn, "SELECT SUM(pph21_dipotong) as total FROM pph21_tidak_final $filter_pph_tf");
$total_pph_tidak_final = mysqli_fetch_assoc($q_pph_tidak_final)['total'] ?: 0;

$grand_total_pajak = $total_pph_tetap + $total_pph_final + $total_pph_tidak_final;

// 2. DATA UNTUK CHART BULANAN (GRAFIK BAR)
$chart_bulan = [];
$chart_nilai_tetap = [];
$chart_nilai_tidak_tetap = [];

for ($m = 1; $m <= 12; $m++) {
    $nama_bulan = date('M', mktime(0, 0, 0, $m, 10));
    $chart_bulan[] = "'$nama_bulan'";

    // Nilai Pajak Tetap per bulan
    $f_bln_tetap = ($role_login == 'admin') ? "WHERE periode_bulan = '$m' AND periode_tahun = '$tahun_ini'" : "WHERE periode_bulan = '$m' AND periode_tahun = '$tahun_ini' AND id_pegawai = '$id_login'";
    $q_bln_tetap = mysqli_query($conn, "SELECT SUM(pph21_dipotong) as total FROM pph21_bulanan $f_bln_tetap");
    $chart_nilai_tetap[] = mysqli_fetch_assoc($q_bln_tetap)['total'] ?: 0;

    // Nilai Pajak Tidak Final per bulan
    $f_bln_tf = ($role_login == 'admin') ? "WHERE MONTH(tanggal_potong) = '$m' AND YEAR(tanggal_potong) = '$tahun_ini'" : "WHERE MONTH(tanggal_potong) = '$m' AND YEAR(tanggal_potong) = '$tahun_ini' AND npwp = '$npwp_login'";
    $q_bln_tidak_tetap = mysqli_query($conn, "SELECT SUM(pph21_dipotong) as total FROM pph21_tidak_final $f_bln_tf");
    $chart_nilai_tidak_tetap[] = mysqli_fetch_assoc($q_bln_tidak_tetap)['total'] ?: 0;
}

$str_chart_bulan = implode(",", $chart_bulan);
$str_chart_tetap = implode(",", $chart_nilai_tetap);
$str_chart_tidak_tetap = implode(",", $chart_nilai_tidak_tetap);

// 3. DATA UNTUK DOUGHNUT CHART (DISTRIBUSI OBJEK PAJAK TIDAK FINAL)
// Khusus chart ini, jika role = user, mungkin chart ini kosong karena mereka bukan 'Bukan Pegawai' (Distributor/Ahli).
$q_distribusi = mysqli_query($conn, "SELECT kode_objek_pajak, SUM(pph21_dipotong) as total FROM pph21_tidak_final $filter_pph_tf GROUP BY kode_objek_pajak");
$label_distribusi = [];
$nilai_distribusi = [];
while ($d = mysqli_fetch_assoc($q_distribusi)) {
    $label_distribusi[] = "'" . $d['kode_objek_pajak'] . "'";
    $nilai_distribusi[] = $d['total'];
}
if (empty($label_distribusi)) {
    $label_distribusi = ["'Belum Ada Data'"];
    $nilai_distribusi = [0]; // Set 0 agar grafik kosong rapi
}
$str_label_distribusi = implode(",", $label_distribusi);
$str_nilai_distribusi = implode(",", $nilai_distribusi);

?>


<div id="layoutSidenav_content">
    <main>
        <!-- Header Page -->
        <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="activity"></i></div>
                                Dashboard Eksekutif YASPA
                            </h1>
                            <div class="page-header-subtitle">Ringkasan analitik dan distribusi penerimaan pajak tahun <?= $tahun_ini ?></div>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <button class="btn btn-white p-3" id="reportrange">
                                <i class="me-2 text-primary" data-feather="calendar"></i>
                                <span>Tahun <?= $tahun_ini ?></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        
        <!-- Main Content -->
        <div class="container-xl px-4 mt-n10">
            
            <!-- BARIS 1: KARTU RINGKASAN WIDGETS -->
            <div class="row">
                <!-- Kartu 1: Total Pegawai -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="me-3">
                                    <div class="small fw-bold text-primary mb-1">Total Database Pegawai</div>
                                    <div class="fs-4 fw-bold text-dark"><?= number_format($r_pegawai, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Orang</span></div>
                                </div>
                                <div class="bg-primary-soft text-primary p-3 rounded-3">
                                    <i data-feather="users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="small text-muted"><i class="fas fa-angle-right me-1"></i> Data terekam di sistem</div>
                        </div>
                    </div>
                </div>

                <!-- Kartu 2: Pajak Pegawai Tetap -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="me-3">
                                    <div class="small fw-bold text-success mb-1">Pajak Pegawai Tetap</div>
                                    <div class="fs-4 fw-bold text-dark">Rp <?= number_format($total_pph_tetap, 0, ',', '.') ?></div>
                                </div>
                                <div class="bg-success-soft text-success p-3 rounded-3">
                                    <i data-feather="building"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="small text-muted"><i class="fas fa-angle-right me-1"></i> PPh 21 Bulanan (TER)</div>
                        </div>
                    </div>
                </div>

                <!-- Kartu 3: Pajak Tidak Finalll -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-white border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="me-3">
                                    <div class="small fw-bold text-info mb-1">Pajak Tidak Final</div>
                                    <div class="fs-4 fw-bold text-dark">Rp <?= number_format($total_pph_tidak_final, 0, ',', '.') ?></div>
                                </div>
                                <div class="bg-info-soft text-info p-3 rounded-3">
                                    <i data-feather="briefcase"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="small text-muted"><i class="fas fa-angle-right me-1"></i> Non-Tetap / Tenaga Ahli</div>
                        </div>
                    </div>
                </div>

                <!-- Kartu 4: Total Penerimaan -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card bg-danger border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="me-3 text-white">
                                    <div class="small fw-bold text-white-50 mb-1">Total Penerimaan Pajak (<?= $tahun_ini ?>)</div>
                                    <div class="fs-4 fw-bold">Rp <?= number_format($grand_total_pajak, 0, ',', '.') ?></div>
                                </div>
                                <div class="bg-white-soft text-white p-3 rounded-3">
                                    <i data-feather="dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="small text-white-50"><i class="fas fa-angle-right me-1"></i> Seluruh setoran PPh 21</div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </main>
    
    <!-- Script Chart.js Bawaan SB Admin Pro -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
    
    <script>
        // Set default font configuration
        Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        // 1. INisialisasi Bar Chart (Tren Bulanan)
        var ctx = document.getElementById("myBarChart");
        var myBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?= $str_chart_bulan ?>],
                datasets: [
                    {
                        label: "Pegawai Tetap (Rp)",
                        backgroundColor: "rgba(0, 97, 242, 1)",
                        hoverBackgroundColor: "rgba(0, 97, 242, 0.9)",
                        borderColor: "#4e73df",
                        data: [<?= $str_chart_tetap ?>],
                    },
                    {
                        label: "Tidak Final / Ahli (Rp)",
                        backgroundColor: "rgba(0, 172, 105, 1)",
                        hoverBackgroundColor: "rgba(0, 172, 105, 0.9)",
                        borderColor: "#1cc88a",
                        data: [<?= $str_chart_tidak_tetap ?>],
                    }
                ],
            },
            options: {
                maintainAspectRatio: false,
                layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
                scales: {
                    xAxes: [{
                        time: { unit: 'month' },
                        gridLines: { display: false, drawBorder: false },
                        ticks: { maxTicksLimit: 12 }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            callback: function(value, index, values) {
                                return 'Rp ' + number_format(value);
                            }
                        },
                        gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
                    }],
                },
                legend: { display: true },
                tooltips: {
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': Rp ' + number_format(tooltipItem.yLabel);
                        }
                    }
                },
            }
        });

        // 2. Inisialisasi Doughnut Chart (Distribusi Pajak)
        var ctx2 = document.getElementById("myPieChart");
        var myPieChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: [<?= $str_label_distribusi ?>],
                datasets: [{
                    data: [<?= $str_nilai_distribusi ?>],
                    backgroundColor: ['#0061f2', '#00ac69', '#f4a100', '#e81500', '#393ed5'],
                    hoverBackgroundColor: ['#0053d0', '#009058', '#d68d00', '#c81200', '#2f34bb'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }],
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var value = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return 'Rp ' + number_format(value);
                        }
                    }
                },
                legend: { display: true, position: 'bottom' },
                cutoutPercentage: 70,
            },
        });

        // Fungsi format angka (helper untuk tooltip chart)
        function number_format(number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) { s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep); }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }
    </script>

<?php require 'layout/footer.php'; ?>