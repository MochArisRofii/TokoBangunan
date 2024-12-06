<?php
session_start();
include('koneksi.php');

// Cek apakah pengguna sudah login
if (!isset($_SESSION['IdUser'])) {
    header("Location: login.php"); // Jika belum login, arahkan ke halaman login
    exit();
}

// Ambil data pengguna yang login
$userId = $_SESSION['IdUser'];
$username = $_SESSION['Username'];
$nama = $_SESSION['Nama'];

// Nonaktifkan ONLY_FULL_GROUP_BY (opsional, jika diperlukan)
$conn->query("SET SESSION sql_mode = ''");

// Ambil data produk dan stok produk dari database
$queryProduk = "SELECT NamaProduk, SUM(Stok) AS JumlahStok FROM produk GROUP BY NamaProduk";
$resultProduk = $conn->query($queryProduk);

// Simpan data produk dan stoknya untuk grafik
$produk = [];
$stok = [];
if ($resultProduk) {
    while ($row = $resultProduk->fetch_assoc()) {
        $produk[] = $row['NamaProduk'];
        $stok[] = $row['JumlahStok'];
    }
} else {
    echo "Error: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Toko Bangunan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Tambahkan Chart.js CDN -->
    <style>
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Navbar Vertikal di sebelah kiri */
        .navbar {
            width: 200px;
            background-color: #5a67d8;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: fixed;
            height: 100%;
        }

        .navbar h1 {
            color: #fff;
            font-size: 24px;
            margin-bottom: 30px;
        }

        .navbar a {
            color: #fff;
            text-decoration: none;
            padding: 10px 15px;
            background-color: #4a5ab8;
            border-radius: 5px;
            margin-bottom: 10px;
            width: 100%;
            text-align: left;
            transition: background-color 0.3s ease;
        }

        .navbar a:hover {
            background-color: #333;
        }

        /* Konten utama */
        .main-content {
            margin-left: 220px;
            padding: 20px;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Grafik dan Tabel */
        .chart-container,
        .table-container {
            margin: 20px auto;
            width: 80%;
            /* Lebar konten yang terpusat */
            max-width: 900px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: center;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        table th {
            background-color: #5a67d8;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>Toko Bangunan</h1>
        <a href="index.php">Home</a>
        <a href="produk.php">Produk</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="lihat_transaksi.php">Lihat Transaksi</a>
        <a href="laporan.php">Laporan</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Konten utama -->
    <div class="main-content">
        <h2>Selamat datang, <?php echo htmlspecialchars($nama); ?></h2>
        <h3>Grafik Stok Produk</h3>

        <!-- Grafik Stok Produk -->
        <div class="chart-container">
            <canvas id="produkChart"></canvas>
        </div>

        <!-- Tabel Produk -->
        <h3>Daftar Produk dan Stoknya</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produk as $index => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item); ?></td>
                            <td><?php echo $stok[$index]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script untuk membuat grafik dengan Chart.js -->
    <script>
        // Data dari PHP
        const produk = <?php echo json_encode($produk); ?>; // Nama Produk
        const stok = <?php echo json_encode($stok); ?>;     // Jumlah Stok

        // Konfigurasi Chart.js
        const ctx = document.getElementById('produkChart').getContext('2d');
        const produkChart = new Chart(ctx, {
            type: 'bar', // Tipe grafik batang (bar)
            data: {
                labels: produk, // Nama produk sebagai label
                datasets: [{
                    label: 'Jumlah Stok Produk',
                    data: stok, // Jumlah stok produk
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Warna latar belakang batang
                    borderColor: 'rgba(75, 192, 192, 1)', // Warna batas batang
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true // Mulai dari 0 di sumbu Y
                    }
                }
            }
        });
    </script>
</body>

</html>