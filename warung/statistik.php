// Statistik keuntungan, kerugian, dan penjualan
function hitungStatistik() {
    global $conn;
    $result = $conn->query("SELECT SUM(terjual * harga_jual) AS total_pendapatan, SUM(terjual * harga_beli) AS total_modal, SUM(terjual) AS total_terjual FROM barang");
    $data = $result->fetch_assoc();
    
    $keuntungan = $data['total_pendapatan'] - $data['total_modal'];

    echo "<h2>Statistik Keuangan</h2>";
    echo "Total Barang Terjual: " . ($data['total_terjual'] ?? 0) . "<br>";
    echo "Total Pendapatan: Rp " . number_format($data['total_pendapatan'] ?? 0, 2) . "<br>";
    echo "Total Modal: Rp " . number_format($data['total_modal'] ?? 0, 2) . "<br>";
    echo "Total Keuntungan: Rp " . number_format($keuntungan, 2) . "<br>";
}
