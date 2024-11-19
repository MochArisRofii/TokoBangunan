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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Toko Bangunan</title>
</head>
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
        margin-left: 220px; /* Memberikan ruang untuk navbar */
        padding: 20px;
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        flex-grow: 1;
    }

    h2 {
        color: #333;
        margin-bottom: 20px;
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

    <!-- Konten utama -->
    <div class="main-content">
        <h2>Selamat datang, <?php echo htmlspecialchars($nama); ?></h2>
    </div>
</body>

</html>
