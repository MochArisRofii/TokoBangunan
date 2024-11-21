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
    <title>Laporan Ringkas</title>
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
            display: flex;
            min-height: 100vh;
        }

        /* Navbar vertikal di sebelah kiri */
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
            margin-bottom: 20px;
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
        .container {
            margin-left: 220px;
            /* Memberikan ruang untuk navbar */
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            flex-grow: 1;
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