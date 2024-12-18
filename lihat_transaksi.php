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

// Ambil filter dari URL jika ada
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Menentukan query berdasarkan filter
$whereClause = ""; // Kondisi filter

switch ($filter) {
    case 'day':
        $whereClause = "WHERE DATE(t.Tanggal) = CURDATE()"; // Filter hasil penjualan hari ini
        break;
    case 'week':
        $whereClause = "WHERE t.Tanggal >= CURDATE() - INTERVAL 1 WEEK"; // Hasil penjualan 1 minggu
        break;
    case 'month':
        $whereClause = "WHERE t.Tanggal >= CURDATE() - INTERVAL 1 MONTH"; // Hasil penjualan 1 bulan
        break;
    case 'year':
        $whereClause = "WHERE t.Tanggal >= CURDATE() - INTERVAL 1 YEAR"; // Hasil penjualan 1 tahun
        break;
    default:
        // Jika tidak ada filter, tampilkan semua transaksi
        break;
}

// Query untuk mengambil data transaksi beserta detail barang berdasarkan filter
$sql = "
    SELECT 
        t.IdTransaksi,
        t.Tanggal,
        t.TotalBayar,
        u.Nama AS NamaUser,
        ti.IdProduk,
        p.NamaProduk,
        ti.Jumlah,
        ti.Subtotal
    FROM transaksi t
    LEFT JOIN users u ON t.IdUser = u.IdUser
    LEFT JOIN transaksi_item ti ON t.IdTransaksi = ti.IdTransaksi
    LEFT JOIN produk p ON ti.IdProduk = p.IdProduk
    $whereClause
    ORDER BY t.Tanggal DESC, t.IdTransaksi ASC
";
$result = $conn->query($sql);

// Data transaksi diolah ke dalam array
$dataTransaksi = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $idTransaksi = $row['IdTransaksi'];
        if (!isset($dataTransaksi[$idTransaksi])) {
            $dataTransaksi[$idTransaksi] = [
                'Tanggal' => $row['Tanggal'],
                'NamaUser' => $row['NamaUser'],
                'TotalBayar' => $row['TotalBayar'],
                'Barang' => [],
                'TotalHargaBarang' => 0, // Inisialisasi total harga barang
            ];
        }
        // Menambahkan produk ke transaksi
        $dataTransaksi[$idTransaksi]['Barang'][] = [
            'NamaProduk' => $row['NamaProduk'],
            'Jumlah' => $row['Jumlah'],
            'Subtotal' => $row['Subtotal']
        ];

        // Menambahkan subtotal produk ke total harga barang
        $dataTransaksi[$idTransaksi]['TotalHargaBarang'] += $row['Subtotal'];
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Transaksi</title>
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
</head>
<style>
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
    }

    .navbar {
        width: 200px;
        background-color: #5a67d8;
        height: 100vh;
        padding: 20px;
        position: fixed;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
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
        width: 100%;
        background-color: #4a5ab8;
        border-radius: 5px;
        margin: 5px 0;
        transition: background-color 0.3s;
        text-align: left;
    }

    .navbar a:hover {
        background-color: #333;
    }

    .content {
        margin-left: 220px;
        padding: 20px;
        width: calc(100% - 220px);
    }

    h3 {
        text-align: center;
        margin-bottom: 20px;
    }

    .transaksi-container {
        margin-bottom: 30px;
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
        text-align: left;
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

    <!-- Main Content -->
    <div class="content">
        <h3>Daftar Transaksi</h3>

        <!-- Filter Form -->
        <!-- Filter Form -->
        <form action="" method="GET" style="margin-bottom: 20px;">
            <label for="filter" style="display: flex; align-items: center; font-weight: bold; margin-bottom: 10px;">
                <span class="fa fa-calendar" style="margin-right: 10px;"></span> Filter Transaksi:
            </label>
            <div style="display: flex; align-items: center;">
                <select name="filter" id="filter" style="
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
            font-size: 14px;
            background-color: #fff;
            width: 200px;">
                    <option value="day" <?= $filter == 'day' ? 'selected' : ''; ?>>Hasil Penjualan Hari Ini</option>
                    <option value="week" <?= $filter == 'week' ? 'selected' : ''; ?>>Hasil Penjualan 1 Minggu</option>
                    <option value="month" <?= $filter == 'month' ? 'selected' : ''; ?>>Hasil Penjualan 1 Bulan</option>
                    <option value="year" <?= $filter == 'year' ? 'selected' : ''; ?>>Hasil Penjualan 1 Tahun</option>
                </select>
                <button type="submit" style="
            padding: 10px 20px;
            background-color: #5a67d8;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;">
                    <span class="fa fa-search" style="margin-right: 5px;"></span> Filter
                </button>
            </div>
        </form>


        <!-- Menampilkan Data Transaksi -->
        <?php if (count($dataTransaksi) > 0): ?>
            <?php foreach ($dataTransaksi as $idTransaksi => $transaksi): ?>
                <div class="transaksi-container">
                    <h4>Transaksi #<?= $idTransaksi ?></h4>
                    <p>
                        <strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($transaksi['Tanggal'])) ?><br>
                        <strong>User:</strong> <?= $transaksi['NamaUser'] ?: "Tidak Diketahui" ?><br>
                        <strong>Total Bayar:</strong> Rp<?= number_format($transaksi['TotalBayar'], 0, ',', '.') ?>
                    </p>

                    <table>
                        <thead>
                            <tr>
                                <th>Nama Produk</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transaksi['Barang'] as $barang): ?>
                                <tr>
                                    <td><?= $barang['NamaProduk'] ?: "Produk Tidak Diketahui" ?></td>
                                    <td><?= $barang['Jumlah'] ?></td>
                                    <td>Rp<?= number_format($barang['Subtotal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Baris Total Harga Barang -->
                            <tr>
                                <td colspan="2" style="text-align: right; font-weight: bold;">Total Harga Barang</td>
                                <td>Rp<?= number_format($transaksi['TotalHargaBarang'], 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada transaksi untuk periode yang dipilih.</p>
        <?php endif; ?>
    </div>
</body>

</html>