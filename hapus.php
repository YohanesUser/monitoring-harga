<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM barang WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Barang berhasil dihapus!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Barang tidak ditemukan!'); window.location='index.php';</script>";
    }

    $stmt->close();
    exit();
}
?>
