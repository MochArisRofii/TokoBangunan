<?php
session_start();
include('koneksi.php');

// Cek apakah pengguna sudah login
if (!isset($_SESSION['IdUser'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna yang login
$userId = $_SESSION['IdUser'];
$username = $_SESSION['Username'];
$nama = $_SESSION['Nama'];

// Memastikan nama pengguna ada dalam sesi
if (isset($_SESSION['NamaUsers'])) {
    $NamaUsers = $_SESSION['NamaUsers'];
} else {
    // Jika 'Name' tidak ada dalam session, bisa gunakan nama yang sudah ada
    $NamaUsers = $nama;
}

// Query untuk menghitung total penghasilan dan jumlah transaksi berdasarkan periode
$sqlLaporan = "
    SELECT
        COUNT(t.IdTransaksi) AS JumlahTransaksi,
        SUM(t.TotalBayar) AS TotalPenghasilan,
        DATE_FORMAT(t.Tanggal, '%Y-%m-%d') AS Periode
    FROM transaksi t
    GROUP BY DATE_FORMAT(t.Tanggal, '%Y-%m-%d')
    ORDER BY Periode DESC
";
$resultLaporan = $conn->query($sqlLaporan);

// Data laporan transaksi
$dataLaporan = [];
if ($resultLaporan->num_rows > 0) {
    while ($row = $resultLaporan->fetch_assoc()) {
        $dataLaporan[] = [
            'Periode' => $row['Periode'],
            'JumlahTransaksi' => $row['JumlahTransaksi'],
            'TotalPenghasilan' => $row['TotalPenghasilan'],
        ];
    }
}

// Query untuk barang paling laku
$sqlProdukTerlaris = "
    SELECT p.NamaProduk, SUM(ti.Jumlah) AS TotalJumlah
    FROM transaksi_item ti
    INNER JOIN produk p ON ti.IdProduk = p.IdProduk
    GROUP BY p.IdProduk
    ORDER BY TotalJumlah DESC
    LIMIT 1
";
$resultProdukTerlaris = $conn->query($sqlProdukTerlaris);
$produkTerlaris = $resultProdukTerlaris->fetch_assoc();

// Query untuk barang paling tidak laku
$sqlProdukTidakLaku = "
    SELECT p.NamaProduk, SUM(ti.Jumlah) AS TotalJumlah
    FROM transaksi_item ti
    INNER JOIN produk p ON ti.IdProduk = p.IdProduk
    GROUP BY p.IdProduk
    ORDER BY TotalJumlah ASC
    LIMIT 1
";
$resultProdukTidakLaku = $conn->query($sqlProdukTidakLaku);
$produkTidakLaku = $resultProdukTidakLaku->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Laporan Ringkas</title>
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

        /* Main content styling */
        .container {
            margin-left: 250px;
            /* Offset for the navbar */
            padding: 20px;
            width: calc(100% - 250px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #5a67d8;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #ddd;
        }

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

        .container-1 {
            display: flex;
            flex-direction: column;
            /* Untuk tata letak vertikal */
            align-items: center;
            /* Meratakan ke tengah horizontal */
            justify-content: flex-start;
            /* Mulai dari atas */
            height: 100%;
            /* Pastikan menggunakan seluruh tinggi kontainer */
            padding-top: 20px;
            /* Jarak dari atas */
        }

        /* Gaya untuk tata letak user-info */
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            /* Meratakan elemen ke tengah */
            text-align: center;
            margin-top: 20px;
        }

        /* Gaya untuk foto pengguna */
        .user-photo {
            width: 100px;
            /* Foto lebih besar */
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            object-fit: cover;
            border: 2px solid #fff;
            /* Bingkai putih */
        }

        /* Gaya untuk username */
        .username {
            color: #fff;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar container-1">
        <h1>Toko Bangunan</h1>
        <a href="index.php"><i class="fa-solid fa-house"></i> Home</a>
        <a href="produk.php"><i class="fa-solid fa-screwdriver-wrench"></i> Produk</a>
        <a href="transaksi.php"><i class="fa-solid fa-cart-plus"></i> Transaksi</a>
        <a href="lihat_transaksi.php"><i class="fa-solid fa-eye"></i> Lihat Transaksi</a>
        <a href="laporan.php"><i class="fa-solid fa-square-poll-horizontal"></i> Laporan</a>
        <a href="logout.php" class="logout-button"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        <div class="user-info">
            <img src="./image/foto.jpeg" alt="Foto User" class="user-photo">
            <span class="username"><?php echo htmlspecialchars($NamaUsers); ?></span>
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="container">
        <h3>Laporan Ringkas</h3>

        <!-- Tabel Laporan -->
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Transaksi</th>
                    <th>Total Penghasilan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($dataLaporan) > 0): ?>
                    <?php foreach ($dataLaporan as $laporan): ?>
                        <tr>
                            <td><?= date('d-m-Y', strtotime($laporan['Periode'])) ?></td>
                            <td><?= $laporan['JumlahTransaksi'] ?></td>
                            <td>Rp<?= number_format($laporan['TotalPenghasilan'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Tidak ada data laporan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Laporan Barang Paling Laku dan Tidak Laku -->
        <h3>Barang Paling Laku dan Tidak Laku</h3>
        <table>
            <thead>
                <tr>
                    <th>Jenis Laporan</th>
                    <th>Nama Barang</th>
                    <th>Total Jumlah Terjual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Barang Paling Laku</td>
                    <td><?= $produkTerlaris['NamaProduk'] ?? 'Tidak ada data' ?></td>
                    <td><?= $produkTerlaris['TotalJumlah'] ?? '0' ?></td>
                </tr>
                <tr>
                    <td>Barang Paling Tidak Laku</td>
                    <td><?= $produkTidakLaku['NamaProduk'] ?? 'Tidak ada data' ?></td>
                    <td><?= $produkTidakLaku['TotalJumlah'] ?? '0' ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>