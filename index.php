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
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Tambahkan Chart.js CDN -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Navbar Vertikal */
        .navbar {
            width: 250px;
            background: linear-gradient(135deg, #4a67d8, #667eea);
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .navbar h1 {
            color: #fff;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
            width: 100%;
        }

        .navbar a {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            padding: 12px 15px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-bottom: 10px;
            width: 100%;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .navbar a i {
            margin-right: 10px;
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .navbar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .navbar a:hover i {
            transform: scale(1.2);
        }

        /* Konten utama */
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        h2 {
            color: #4a67d8;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 40px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .section h3 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Grafik */
        .chart-container {
            margin: 0 auto;
            width: 100%;
            max-width: 800px;
        }

        /* Tabel */
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: left;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        table th {
            background-color: #4a67d8;
            color: #fff;
            font-weight: bold;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #eef1f9;
        }

        table td {
            color: #555;
        }

        /* Tombol Logout */
        .logout-button {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 15px;
            background-color: #e53e3e;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #c53030;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>Toko Bangunan</h1>
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <a href="produk.php"><i class="fa-solid fa-screwdriver-wrench"></i> Produk</a>
        <a href="transaksi.php"><i class="fa-solid fa-cart-plus"></i> Transaksi</a>
        <a href="lihat_transaksi.php"><i class="fa-solid fa-eye"></i> Lihat Transaksi</a>
        <a href="laporan.php"><i class="fa-solid fa-square-poll-horizontal"></i> Laporan</a>
        <a href="logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>

    <!-- Konten utama -->
    <div class="main-content">
        <div class="section">
            <h2>Selamat datang, <?php echo htmlspecialchars($nama); ?></h2>
            <h3>Grafik Stok Produk</h3>

            <!-- Grafik Stok Produk -->
            <div class="chart-container">
                <canvas id="produkChart"></canvas>
            </div>
        </div>

        <div class="section">
            <h3>Daftar Produk dan Stoknya</h3>
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
                    backgroundColor: 'rgba(75, 192, 192, 0.6)', // Warna latar belakang batang
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
