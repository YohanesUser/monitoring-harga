<?php
include 'config.php';

if (isset($_POST['id']) && isset($_POST['change'])) {
    $id = intval($_POST['id']);
    $change = intval($_POST['change']);

    // Update stok barang
    $conn->query("UPDATE barang SET stok = stok + $change WHERE id = $id");

    // Mengambil stok terbaru
    $result = $conn->query("SELECT stok FROM barang WHERE id = $id");
    $row = $result->fetch_assoc();
    
    echo json_encode(['stok' => $row['stok']]);
}
?>
