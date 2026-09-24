<?php
session_start();
require 'config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $npwp = mysqli_real_escape_string($conn, $_POST['npwp']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Cari data pegawai berdasarkan NPWP
    $query = "SELECT * FROM pegawai WHERE npwp = '$npwp' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 1) {
        $data = mysqli_fetch_assoc($result);

        // Buat Session
        $_SESSION['id_pegawai'] = $data['id_pegawai'];
        $_SESSION['nama']       = $data['nama'];
        $_SESSION['npwp']       = $data['npwp'];
        $_SESSION['role']       = $data['role']; // 'admin' atau 'user'

        // Arahkan ke Dashboard
        header("Location: index.php");
        exit();
    } else {
        // Jika gagal login
        $_SESSION['error'] = "NPWP atau Password salah!";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>