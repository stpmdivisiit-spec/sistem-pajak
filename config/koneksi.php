<?php
$host     = "localhost";
$username = "root"; // Sesuaikan dengan user database Anda
$password = "";     // Sesuaikan dengan password database Anda
$database = "pajak-yaspa";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi Database Gagal: " . mysqli_connect_error());
}
?>