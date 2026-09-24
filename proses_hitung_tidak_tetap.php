<?php
// File: proses_hitung_tidak_tetap.php
session_start();
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hitung_tidak_tetap'])) {
    $id_pegawai = $_POST['id_pegawai'];
    $kode_objek_pajak = '21-100-03';
    $upah_harian = (float) $_POST['upah_harian'];
    $jumlah_hari_kerja = (int) $_POST['jumlah_hari_kerja'];
    $tanggal_proses = date('Y-m-d');

    // Total Bruto = Upah Sehari x Jumlah Hari Kerja
    $penghasilan_bruto = $upah_harian * $jumlah_hari_kerja;
    
    $tarif_ter = 0;
    $pph21_dipotong = 0;

    // Logika TER Harian PMK No. 168 Tahun 2023
    if ($upah_harian <= 450000) {
        $tarif_ter = 0; // 0%
    } elseif ($upah_harian > 450000 && $upah_harian <= 2500000) {
        $tarif_ter = 0.5; // 0,5%
    } else {
        // Jika upah harian > 2.500.000, aturan PMK 168 menggunakan 50% x Bruto x Tarif PPh 17 (skema khusus). 
        // Di sini kita asumsikan untuk peringatan/simulasi.
        $tarif_ter = 2.5; // Angka Simulasi Tarif PPh Ps 17 atas 50% PKP
    }

    // Hitung Pajak PPh 21
    $pph21_dipotong = $penghasilan_bruto * ($tarif_ter / 100);

    // Simpan ke Database
    $stmt = $conn->prepare("INSERT INTO pph21_tidak_tetap (id_pegawai, kode_objek_pajak, tanggal_proses, upah_harian, jumlah_hari_kerja, penghasilan_bruto, tarif_ter_harian, pph21_dipotong) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdiddd", $id_pegawai, $kode_objek_pajak, $tanggal_proses, $upah_harian, $jumlah_hari_kerja, $penghasilan_bruto, $tarif_ter, $pph21_dipotong);
    $stmt->execute();

    $_SESSION['sukses'] = "Perhitungan PPh 21 Pegawai Tidak Tetap (TER Harian) berhasil disimpan.";
    header("Location: pph21_tidak_tetap.php");
    exit();
}
?>