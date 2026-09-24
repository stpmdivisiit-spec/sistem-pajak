<?php
// File: proses_hitung_final.php
session_start();
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hitung_final'])) {
    $id_pegawai = $_POST['id_pegawai'];
    $kode_objek_pajak = $_POST['kode_objek_pajak'];
    $penghasilan_bruto = (float) $_POST['penghasilan_bruto'];
    $akumulasi_sebelumnya = (float) $_POST['akumulasi_sebelumnya']; // Jika dibayar bertahap
    $tanggal_bayar = date('Y-m-d');

    $total_bruto = $penghasilan_bruto + $akumulasi_sebelumnya;
    $pph21_total_baru = 0;
    
    // Logika Perhitungan Tarif Progresif
    if ($kode_objek_pajak == '21-401-01') {
        // --- TARIF UANG PESANGON (PP 68/2009) ---
        // 0 - 50 Juta = 0%
        // >50 Juta - 100 Juta = 5%
        // >100 Juta - 500 Juta = 15%
        // >500 Juta = 25%

        $sisa_bruto = $total_bruto;

        // Tier 4: Di atas 500 Juta (25%)
        if ($sisa_bruto > 500000000) {
            $pkp_tier4 = $sisa_bruto - 500000000;
            $pph21_total_baru += ($pkp_tier4 * 0.25);
            $sisa_bruto = 500000000;
        }
        // Tier 3: 100 Juta - 500 Juta (15%)
        if ($sisa_bruto > 100000000) {
            $pkp_tier3 = $sisa_bruto - 100000000;
            $pph21_total_baru += ($pkp_tier3 * 0.15);
            $sisa_bruto = 100000000;
        }
        // Tier 2: 50 Juta - 100 Juta (5%)
        if ($sisa_bruto > 50000000) {
            $pkp_tier2 = $sisa_bruto - 50000000;
            $pph21_total_baru += ($pkp_tier2 * 0.05);
            $sisa_bruto = 50000000;
        }
        // Tier 1: 0 - 50 Juta (0%)
        // $pph21_total_baru += (0 * 0);

    } elseif ($kode_objek_pajak == '21-401-02') {
        // --- TARIF MANFAAT PENSIUN/JHT/THT SEKELIGUS (PP 68/2009) ---
        // 0 - 50 Juta = 0%
        // >50 Juta = 5%

        $sisa_bruto = $total_bruto;

        if ($sisa_bruto > 50000000) {
            $pkp_atas = $sisa_bruto - 50000000;
            $pph21_total_baru += ($pkp_atas * 0.05);
        }
    }

    // Jika ada pembayaran sebelumnya (akumulasi), kurangi pajak yang sudah dipotong (simulasi sederhana)
    // Untuk YASPA, kita simpan nilai pajak terutang saat ini saja.
    $pph21_final = $pph21_total_baru;

    // Simpan ke Database
    $stmt = $conn->prepare("INSERT INTO pph21_final (id_pegawai, kode_objek_pajak, tanggal_bayar, penghasilan_bruto, akumulasi_sebelumnya, pph21_dipotong) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issddd", $id_pegawai, $kode_objek_pajak, $tanggal_bayar, $penghasilan_bruto, $akumulasi_sebelumnya, $pph21_final);
    $stmt->execute();

    $_SESSION['sukses'] = "Perhitungan PPh 21 Final berhasil disimpan.";
    header("Location: pph21_final.php");
    exit();
}
?>