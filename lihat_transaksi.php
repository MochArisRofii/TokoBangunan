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
                'IdTransaksi' => $row['IdTransaksi'], // Menambahkan IdTransaksi
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<style>
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
    .content {
        margin-left: 250px;
        /* Offset for the navbar */
        padding: 20px;
        width: calc(100% - 250px);
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

    .container {
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

<body>
    <!-- Navbar -->
    <div class="navbar container">
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

    <!-- Main Content -->
    <div class="content">
        <h3>Daftar Transaksi</h3>

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
                    <option value="day" <?= $filter == 'day' ? 'selected' : ''; ?>>Hari Ini</option>
                    <option value="week" <?= $filter == 'week' ? 'selected' : ''; ?>>Minggu Ini</option>
                    <option value="month" <?= $filter == 'month' ? 'selected' : ''; ?>>Bulan Ini</option>
                    <option value="year" <?= $filter == 'year' ? 'selected' : ''; ?>>Tahun Ini</option>
                    <option value="" <?= $filter == '' ? 'selected' : ''; ?>>Semua Transaksi</option>
                </select>
                <button type="submit"
                    style="padding: 10px 15px; background-color: #4a67d8; color: white; border: none; border-radius: 5px;">Terapkan
                    Filter</button>
            </div>
        </form>

        <?php foreach ($dataTransaksi as $transaksi): ?>
            <div class="transaksi-container">
                <h4>Transaksi ID: <?= $transaksi['IdTransaksi'] ?></h4>
                <p><strong>Tanggal:</strong> <?= date('l, j F Y', strtotime($transaksi['Tanggal'])) ?></p>
                <p><strong>Nama User:</strong> <?= $transaksi['NamaUser'] ?></p>
                <p><strong>Total Bayar:</strong> Rp <?= number_format($transaksi['TotalBayar'], 0, ',', '.') ?></p>

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
                                <td><?= $barang['NamaProduk'] ?></td>
                                <td><?= $barang['Jumlah'] ?></td>
                                <td>Rp <?= number_format($barang['Subtotal'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>

    </div>
</body>

</html>
