<?php
include 'config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$success = "";
$errors = [];
$strukData = "";
$totalHarga = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barang = $_POST['barang']; // Array barang
    $transaksi = [];

    foreach ($barang as $item) {
        $id = $item['id'];
        $jumlah = $item['jumlah'];

        // Ambil data barang
        $stmt = $conn->prepare("SELECT nama_barang, harga_jual, stok FROM barang WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $barangData = $result->fetch_assoc();
        $stmt->close();

        if ($barangData && $barangData['stok'] >= $jumlah) {
            // Update stok & jumlah terjual
            $stmt = $conn->prepare("UPDATE barang SET stok = stok - ?, terjual = terjual + ? WHERE id = ?");
            $stmt->bind_param("iii", $jumlah, $jumlah, $id);
            $stmt->execute();
            $stmt->close();

            // Tambahkan ke transaksi
            $hargaTotal = $jumlah * $barangData['harga_jual'];
            $totalHarga += $hargaTotal;
            $transaksi[] = [
                "nama" => $barangData['nama_barang'],
                "jumlah" => $jumlah,
                "harga" => $barangData['harga_jual'],
                "total" => $hargaTotal
            ];
        } else {
            $errors[] = "Stok tidak mencukupi untuk barang {$barangData['nama_barang']}.";
        }
    }

    if (empty($errors)) {
        $success = "Barang berhasil dijual! <a href='index.php'>Kembali</a>";

        // Generate struk dalam format tabel teks
        $strukData = "=== STRUK BELANJA ===\n";
        $strukData .= "Tanggal: " . date("Y-m-d H:i:s") . "\n";
        $strukData .= "---------------------------------------------\n";
        $strukData .= sprintf("%-20s %-8s %-10s %-10s\n", "Nama Barang", "Jumlah", "Harga", "Total");
        $strukData .= "---------------------------------------------\n";
        foreach ($transaksi as $item) {
            $strukData .= sprintf("%-20s %-8d Rp%-10s Rp%-10s\n", 
                $item['nama'], $item['jumlah'], number_format($item['harga'], 0, ',', '.'), number_format($item['total'], 0, ',', '.')
            );
        }
        $strukData .= "---------------------------------------------\n";
        $strukData .= sprintf("TOTAL: Rp %s\n", number_format($totalHarga, 0, ',', '.'));
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jual Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .form-container { max-width: 600px; margin: auto; }
        .barang-item { display: flex; gap: 10px; margin-bottom: 10px; }
        @media (max-width: 768px) {
            .barang-item { flex-direction: column; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">Jual Barang</h2>
        <div class="card p-4 shadow-sm form-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?= implode("<br>", $errors); ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success"><?= $success; ?></div>
                <button onclick="downloadStruk()" class="btn btn-success mt-3">Download Struk</button>
                <textarea id="strukContent" class="d-none"><?= htmlspecialchars($strukData); ?></textarea>
            <?php endif; ?>

            <form method="post" id="jualForm">
                <div id="barangList">
                    <div class="barang-item">
                        <select name="barang[0][id]" class="form-select" required>
                            <option value="">Pilih Barang</option>
                            <?php
                            $result = $conn->query("SELECT id, nama_barang FROM barang");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['nama_barang']}</option>";
                            }
                            ?>
                        </select>
                        <input type="number" name="barang[0][jumlah]" class="form-control" placeholder="Jumlah" required>
                        <button type="button" class="btn btn-danger remove-item">Hapus</button>
                    </div>
                </div>
                <button type="button" class="btn btn-secondary mt-3" id="addBarang">Tambah Barang</button>
                <button type="submit" class="btn btn-primary mt-3">Jual</button>

                <!-- Menampilkan Total Harga -->
                <?php if (!empty($totalHarga)): ?>
                    <div class="alert alert-info mt-3">
                        <strong>Total Harga: </strong> Rp <?= number_format($totalHarga, 0, ',', '.'); ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let barangCount = 1;

            document.getElementById("addBarang").addEventListener("click", function() {
                let newItem = document.createElement("div");
                newItem.classList.add("barang-item");
                newItem.innerHTML = `   
                    <select name="barang[${barangCount}][id]" class="form-select" required>
                        <option value="">Pilih Barang</option>
                        <?php
                        $result = $conn->query("SELECT id, nama_barang FROM barang");
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['nama_barang']}</option>";
                        }
                        ?>
                    </select>
                    <input type="number" name="barang[${barangCount}][jumlah]" class="form-control" placeholder="Jumlah" required>
                    <button type="button" class="btn btn-danger remove-item">Hapus</button>
                `;
                document.getElementById("barangList").appendChild(newItem);
                barangCount++;
            });

            // Event delegation untuk tombol "Hapus"
            document.getElementById("barangList").addEventListener("click", function(e) {
                if (e.target.classList.contains("remove-item")) {
                    e.target.closest(".barang-item").remove();
                }
            });
        });

        function downloadStruk() {
            let strukText = document.getElementById("strukContent").value;
            let blob = new Blob([strukText], { type: "text/plain" });
            let link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "struk_belanja.txt";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
