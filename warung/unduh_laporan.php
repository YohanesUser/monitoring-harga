<?php
include 'config.php';

// Set header agar file terunduh sebagai teks
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="Laporan_Keuntungan_Hari_Ini.txt"');

// Garis pembatas
$line = str_repeat("=", 65) . "\n";
$dash = str_repeat("-", 65) . "\n";

// Header laporan
echo $line;
echo "                 LAPORAN KEUNTUNGAN HARI INI\n";
echo $line;
echo "Tanggal: " . date("Y-m-d") . "\n";
echo $dash;
echo "| Nama Barang         | Stok Awal | Terjual | Stok Akhir | Keuntungan  |\n";
echo $dash;

// Ambil data barang dari database
$query = "SELECT nama_barang, stok + terjual AS stok_awal, stok, terjual, 
                 (terjual * (harga_jual - harga_beli)) AS keuntungan 
          FROM barang";
$result = $conn->query($query);

$total_terjual = 0;
$total_keuntungan = 0;
$total_barang = 0;
$total_tidak_terjual = 0;

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Menyesuaikan panjang string agar tetap sejajar
        $nama_barang = str_pad(substr($row['nama_barang'], 0, 18), 18, " ");
        $stok_awal = str_pad($row['stok_awal'], 9, " ", STR_PAD_LEFT);
        $terjual = str_pad($row['terjual'], 7, " ", STR_PAD_LEFT);
        $stok_akhir = str_pad($row['stok'], 10, " ", STR_PAD_LEFT);
        $keuntungan = str_pad("Rp " . number_format($row['keuntungan'], 2), 11, " ", STR_PAD_LEFT);
        
        // Tampilkan setiap baris barang
        echo "| $nama_barang | $stok_awal | $terjual | $stok_akhir | $keuntungan |\n";

        // Akumulasi total
        $total_terjual += $row['terjual'];
        $total_keuntungan += $row['keuntungan'];
        $total_barang += $row['stok_awal'];
        $total_tidak_terjual += ($row['stok_awal'] - $row['terjual']);
    }
} else {
    echo "| Tidak ada data barang tersedia.                                      |\n";
}

echo $dash;

// Menyesuaikan panjang teks agar sejajar untuk total
$total_label = str_pad("TOTAL", 18, " ");
$total_stok_awal = str_pad($total_barang, 9, " ", STR_PAD_LEFT);
$total_terjual = str_pad($total_terjual, 7, " ", STR_PAD_LEFT);
$total_tidak_terjual = str_pad($total_tidak_terjual, 10, " ", STR_PAD_LEFT);
$total_keuntungan = str_pad("Rp " . number_format($total_keuntungan, 2), 11, " ", STR_PAD_LEFT);

// Tampilkan total keseluruhan
echo "| $total_label | $total_stok_awal | $total_terjual | $total_tidak_terjual | $total_keuntungan |\n";

echo $line;
?>
