<?php
// File: proses_hitung_tidak_final.php
session_start();
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hitung_tidak_final'])) {
    $nama_penerima = $_POST['nama_penerima'];
    $npwp = $_POST['npwp'] ?: '-';
    $kode_objek_pajak = $_POST['kode_objek_pajak'];
    $penghasilan_bruto = (float) $_POST['penghasilan_bruto'];
    $tanggal_potong = date('Y-m-d');

    $dpp = 0;
    $pph21 = 0;
    $keterangan_tarif = "";

    // 1. LOGIKA UNTUK PEGAWAI TIDAK TETAP (TER HARIAN)
    if ($kode_objek_pajak == '21-100-03') {
        $hari_kerja = (int) $_POST['hari_kerja'];
        if($hari_kerja == 0) $hari_kerja = 1;

        $upah_harian = $penghasilan_bruto / $hari_kerja;
        $dpp = $penghasilan_bruto; // Skema TER, DPP = Bruto

        if ($upah_harian <= 450000) {
            $pph21 = 0;
            $keterangan_tarif = "TER Harian 0%";
        } elseif ($upah_harian > 450000 && $upah_harian <= 2500000) {
            $pph21 = $dpp * 0.005; // 0,5%
            $keterangan_tarif = "TER Harian 0.5%";
        } else {
            // Berlaku tarif Pasal 17 (disederhanakan untuk contoh)
            $pph21 = $dpp * 0.05; 
            $keterangan_tarif = "Tarif Ps. 17";
        }
    } 
    // 2. LOGIKA UNTUK BUKAN PEGAWAI (Tenaga Ahli, Seniman, Distributor, dll)
    else {
        // Aturan PMK 168/2023: DPP = 50% x Penghasilan Bruto
        $dpp = $penghasilan_bruto * 0.50;
        
        $sisa_dpp = $dpp;
        $total_pajak = 0;

        // Tarif Progresif Pasal 17 UU HPP
        // Lapis 4: > 500 Juta (30%)
        if ($sisa_dpp > 500000000) {
            $pkp = $sisa_dpp - 500000000;
            $total_pajak += ($pkp * 0.30);
            $sisa_dpp = 500000000;
        }
        // Lapis 3: > 250 Juta - 500 Juta (25%)
        if ($sisa_dpp > 250000000) {
            $pkp = $sisa_dpp - 250000000;
            $total_pajak += ($pkp * 0.25);
            $sisa_dpp = 250000000;
        }
        // Lapis 2: > 60 Juta - 250 Juta (15%)
        if ($sisa_dpp > 60000000) {
            $pkp = $sisa_dpp - 60000000;
            $total_pajak += ($pkp * 0.15);
            $sisa_dpp = 60000000;
        }
        // Lapis 1: 0 - 60 Juta (5%)
        if ($sisa_dpp > 0) {
            $total_pajak += ($sisa_dpp * 0.05);
        }

        // Kenaikan 20% jika tidak punya NPWP (Opsional sesuai aturan riil)
        if ($npwp == '-' || empty($npwp)) {
            $total_pajak = $total_pajak * 1.20;
            $keterangan_tarif = "50% Bruto x Ps 17 (Non-NPWP +20%)";
        } else {
            $keterangan_tarif = "50% x Bruto x Tarif Ps. 17";
        }

        $pph21 = $total_pajak;
    }

    // Simpan ke DB
    $stmt = $conn->prepare("INSERT INTO pph21_tidak_final (nama_penerima, npwp, kode_objek_pajak, penghasilan_bruto, dpp_pajak, tarif_efektif, pph21_dipotong, tanggal_potong) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddsds", $nama_penerima, $npwp, $kode_objek_pajak, $penghasilan_bruto, $dpp, $keterangan_tarif, $pph21, $tanggal_potong);
    $stmt->execute();

    $_SESSION['sukses'] = "Pajak PPh 21 Tidak Final atas nama $nama_penerima berhasil dihitung!";
    header("Location: pph21_tidak_final.php");
    exit();
}
?>