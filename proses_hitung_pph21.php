<?php
// File: proses_hitung_pph21.php
session_start();
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hitung_pph21'])) {
    $periode_bulan = date('m'); // Bisa diganti form input dinamis
    $periode_tahun = date('Y');

    // Kosongkan data perhitungan di bulan ini agar tidak duplikat saat di-refresh
    mysqli_query($conn, "DELETE FROM pph21_bulanan WHERE periode_bulan='$periode_bulan' AND periode_tahun='$periode_tahun'");

    // Ambil semua data pegawai
    $query = "SELECT * FROM pegawai";
    $result = mysqli_query($conn, $query);

    while ($pegawai = mysqli_fetch_assoc($result)) {
        $id_pegawai = $pegawai['id_pegawai'];
        $gaji_bruto = $pegawai['gaji_bruto'];
        $ptkp = $pegawai['ptkp'];

        // Menentukan Kategori TER Berdasarkan PTKP
        $kategori_ter = '';
        if (in_array($ptkp, ['TK/0', 'TK/1', 'K/0'])) {
            $kategori_ter = 'A';
        } elseif (in_array($ptkp, ['TK/2', 'TK/3', 'K/1', 'K/2'])) {
            $kategori_ter = 'B';
        } elseif (in_array($ptkp, ['K/3'])) {
            $kategori_ter = 'C';
        } else {
             $kategori_ter = 'A'; // Default fallback
        }

        // Logic Dummy/Contoh Menentukan Tarif TER (Persentase)
        // PERHATIAN: Di sistem *production*, Anda HARUS membuat tabel khusus master_tarif_ter
        // yang berisi *range* gaji dan persentase berdasarkan Lampiran PP 58/2023.
        // Di sini kita gunakan simulasi tarif dasar.
        $tarif_ter = 0;
        
        if ($kategori_ter == 'A') {
            if ($gaji_bruto <= 5400000) $tarif_ter = 0;
            elseif ($gaji_bruto <= 5650000) $tarif_ter = 0.25;
            elseif ($gaji_bruto <= 5950000) $tarif_ter = 0.5;
            elseif ($gaji_bruto <= 6300000) $tarif_ter = 0.75;
            elseif ($gaji_bruto <= 6750000) $tarif_ter = 1;
            // ... (dst sesuai tabel TER A)
            else $tarif_ter = 2.5; // Angka Simulasi Gaji 10jt TER A
        } elseif ($kategori_ter == 'B') {
             // ... (Logika rentang gaji TER B)
             $tarif_ter = 2; // Angka Simulasi
        } elseif ($kategori_ter == 'C') {
             // ... (Logika rentang gaji TER C)
             $tarif_ter = 1.5; // Angka Simulasi
        }

        // Hitung Pajak (Skema Gross)
        $pph21 = $gaji_bruto * ($tarif_ter / 100);
        $dpp = $gaji_bruto; // Dalam skema Gross TER, DPP adalah Gaji Bruto itu sendiri

        // Insert Hasil ke Database
        $stmt = $conn->prepare("INSERT INTO pph21_bulanan (id_pegawai, kode_objek_pajak, periode_bulan, periode_tahun, gaji_bruto, status_ptkp, tarif_ter_persen, kategori_ter, pph21_dipotong, dpp_pajak) VALUES (?, '21-100-01', ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidsisdd", $id_pegawai, $periode_bulan, $periode_tahun, $gaji_bruto, $ptkp, $tarif_ter, $kategori_ter, $pph21, $dpp);
        $stmt->execute();
    }

    $_SESSION['sukses'] = "Perhitungan PPh 21 (TER) berhasil dilakukan untuk bulan berjalan!";
    header("Location: pph21_tetap.php");
    exit();
}
?>