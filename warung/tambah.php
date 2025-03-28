<?php
include 'config.php';

// Menampilkan error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Ambil daftar barang dari database
$barang_list = [];
$result = $conn->query("SELECT id, nama_barang FROM barang");
while ($row = $result->fetch_assoc()) {
    $barang_list[] = $row;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aksi = $_POST['aksi'];  // Tambah atau Update
    $stok = $_POST['stok'];

    if ($aksi == "tambah") {
        $nama = trim($_POST['nama']);
        $beli = $_POST['harga_beli'];
        $jual = $_POST['harga_jual'];

        if (empty($nama) || $stok <= 0 || $beli <= 0 || $jual <= 0 || $jual < $beli) {
            $error = "Pastikan semua data diisi dengan benar dan harga jual tidak lebih rendah dari harga beli!";
        } else {
            $stmt = $conn->prepare("INSERT INTO barang (nama_barang, harga_beli, harga_jual, stok) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siii", $nama, $beli, $jual, $stok);
            $stmt->execute();
            $stmt->close();
            $success = "Barang baru berhasil ditambahkan!";
        }
    } else {
        $barang_id = $_POST['barang_id'];
        $stmt = $conn->prepare("SELECT stok FROM barang WHERE id = ?");
        $stmt->bind_param("i", $barang_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $new_stok = $row['stok'] + $stok;
            $update_stmt = $conn->prepare("UPDATE barang SET stok = ? WHERE id = ?");
            $update_stmt->bind_param("ii", $new_stok, $barang_id);
            $update_stmt->execute();
            $update_stmt->close();
            $success = "Stok barang berhasil diperbarui!";
        } else {
            $error = "Barang tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah / Update Stok Barang</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function toggleFields() {
            let aksi = document.getElementById("aksi").value;
            let hargaFields = document.getElementById("harga-fields");
            let tambahField = document.getElementById("tambah-nama");
            let updateField = document.getElementById("update-nama");
            
            if (aksi === "tambah") {
                hargaFields.style.display = "block";
                tambahField.style.display = "block";
                updateField.style.display = "none";
            } else {
                hargaFields.style.display = "none";
                tambahField.style.display = "none";
                updateField.style.display = "block";
            }
        }
    </script>
    <style>
        .form-container { max-width: 500px; margin: auto; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4">Tambah / Update Stok Barang</h2>
        <div class="card p-4 shadow-sm form-container">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error; ?></div>
            <?php elseif (!empty($success)): ?>
                <div class="alert alert-success"><?= $success; ?> <a href="index.php">Kembali</a></div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Pilih Aksi</label>
                    <select name="aksi" id="aksi" class="form-control" onchange="toggleFields()" required>
                        <option value="tambah">Tambah Barang Baru</option>
                        <option value="update">Update Stok Barang</option>
                    </select>
                </div>
                
                <div id="tambah-nama">
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="nama" class="form-control">
                    </div>
                </div>
                
                <div id="update-nama" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Pilih Barang</label>
                        <select name="barang_id" class="form-control">
                            <?php foreach ($barang_list as $barang): ?>
                                <option value="<?= $barang['id']; ?>"><?= $barang['nama_barang']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div id="harga-fields">
                    <div class="mb-3">
                        <label class="form-label">Harga Beli</label>
                        <input type="number" name="harga_beli" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga Jual</label>
                        <input type="number" name="harga_jual" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah Stok</label>
                    <input type="number" name="stok" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Simpan</button>
            </form>
        </div>
        <div class="text-center mt-3">
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
</body>
</html>
