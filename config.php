<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "warung";

$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Periksa apakah sudah reset hari ini
$today = date("Y-m-d");
$check_reset = $conn->query("SELECT COUNT(*) as count FROM barang WHERE last_reset = '$today'");
$row = $check_reset->fetch_assoc();

if ($row['count'] == 0) {
    // Reset jumlah barang terjual ke 0
    $conn->query("UPDATE barang SET terjual = 0, last_reset = '$today'");
}

?>
