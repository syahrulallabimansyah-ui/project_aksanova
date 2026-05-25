<?php
// db.php — Koneksi ke database MySQL
// Sesuaikan host, username, password, dan nama database

$host   = "localhost";
$dbuser = "root";       // username MySQL kamu
$dbpass = "";           // password MySQL kamu
$dbname = "aksa_nova";  // nama database

$conn = mysqli_connect($host, $dbuser, $dbpass, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset agar aman
mysqli_set_charset($conn, "utf8");
?>