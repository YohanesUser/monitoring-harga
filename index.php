<?php
include 'config.php';

// Reset otomatis setiap pukul 00:00
$today = date("Y-m-d");
$check_reset = $conn->query("SELECT COUNT(*) as count FROM barang WHERE last_reset = '$today'");
$row = $check_reset->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("UPDATE barang SET terjual = 0, last_reset = '$today'");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Warung</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media (max-width: 768px) {
            .table thead { display: none; }
            .table, .table tbody, .table tr, .table td { display: block; width: 100%; }
            .table tr { margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; border-radius: 10px; }
            .table td { text-align: right; position: relative; padding-left: 50%; }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 45%;
                text-align: left;
                font-weight: bold;
            }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="text-center mb-4"> Daftar harga Warung</h2>
        
        <div class="text-center mb-4">
    <a href="tambah.php" class="btn btn-primary">Tambah Barang</a>
    <a href="jual.php" class="btn btn-success">Jual Barang</a>
    <a href="unduh_laporan.php" class="btn btn-warning">Unduh Laporan Hari Ini</a>
</div>

        
        <div class="mb-3">
            <input type="text" id="search" class="form-control" placeholder="Cari barang..." onkeyup="searchTable()">
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center" id="barangTable">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama Barang</th>
                        <th>Harga Beli</th>
                        <th>Harga Jual</th>
                        <th>Stok</th>
                        <th>Terjual</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT * FROM barang");
                    while ($row = $result->fetch_assoc()) {
                    ?>
                        <tr>
                            <td data-label="ID"> <?= $row['id'] ?> </td>
                            <td data-label="Nama Barang"> <?= $row['nama_barang'] ?> </td>
                            <td data-label="Harga Beli"> Rp <?= number_format($row['harga_beli']) ?> </td>
                            <td data-label="Harga Jual"> Rp <?= number_format($row['harga_jual']) ?> </td>
                            <td data-label="Stok"> <?= $row['stok'] ?> </td>
                            <td data-label="Terjual"> <?= $row['terjual'] ?> </td>
                            <td data-label="Aksi">
                                <button class="btn btn-success btn-sm" onclick="updateStok(<?= $row['id'] ?>, 1)">+</button>
                                <button class="btn btn-danger btn-sm" onclick="updateStok(<?= $row['id'] ?>, -1)">-</button>
                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
        <h2 class="text-center">Statistik Keuangan</h2>
        <?php
        $result = $conn->query("SELECT 
            COALESCE(SUM(terjual), 0) AS total_terjual, 
            COALESCE(SUM(terjual * harga_jual), 0) AS total_pendapatan, 
            COALESCE(SUM(terjual * harga_beli), 0) AS total_modal 
            FROM barang");
        $data = $result->fetch_assoc();

        $total_terjual = $data['total_terjual'];
        $total_pendapatan = $data['total_pendapatan'];
        $total_modal = $data['total_modal'];
        $keuntungan = $total_pendapatan - $total_modal;
        ?>

        <div class="card shadow-sm p-4">
            <p><strong>Total Barang Terjual:</strong> <?= number_format($total_terjual) ?></p>
            <p><strong>Pendapatan Kotor:</strong> Rp <?= number_format($total_pendapatan, 2) ?></p>
            <p><strong>Total Modal:</strong> Rp <?= number_format($total_modal, 2) ?></p>
            <p><strong>Total Keuntungan:</strong> Rp <?= number_format($keuntungan, 2) ?></p>
        </div>
        
        <h3 class="text-center mt-4">Grafik Keuntungan Per Hari</h3>
        
        <canvas id="keuntunganChart"></canvas>
    </div>

    <script>
        function searchTable() {
            let input = document.getElementById("search").value.toLowerCase();
            let rows = document.querySelectorAll("#barangTable tbody tr");
            rows.forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(input) ? "" : "none";
            });
        }
        
        fetch('get_keuntungan_data.php')
            .then(response => response.json())
            .then(data => {
                let ctx = document.getElementById('keuntunganChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.tanggal,
                        datasets: [{
                            label: 'Keuntungan Harian',
                            data: data.keuntungan,
                            borderColor: 'blue',
                            backgroundColor: 'rgba(0, 0, 255, 0.2)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            });
            function updateStok(id, change) {
    fetch('update_stok.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${id}&change=${change}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.stok !== undefined) {
            document.querySelector(`#barangTable tr td[data-label="ID"]:contains("${id}")`)
                .parentNode.querySelector('td[data-label="Stok"]').innerText = data.stok;
        }
    })
    .catch(error => console.error('Error:', error));
}
    </script>
</body>
</html>
