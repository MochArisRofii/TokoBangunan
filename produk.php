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

// Query untuk menampilkan produk beserta kategori
$sql = "
    SELECT produk.IdProduk, produk.NamaProduk, produk.Deskripsi, produk.Harga, produk.Stok, kategori.NamaKategori
    FROM produk
    LEFT JOIN kategori ON produk.IdKategori = kategori.IdKategori
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Toko Bangunan</title>
    <style>
        /* Basic reset */
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

        /* Navbar styling */
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

        /* Main content styling */
        .content {
            margin-left: 220px;
            /* Offset for the navbar */
            padding: 20px;
            width: calc(100% - 220px);
        }

        h3 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        /* Button style */
        .btn {
            background-color: #5a67d8;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            text-align: center;
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #4a5ab8;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
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

        .action-btn {
            display: flex;
            justify-content: space-evenly;
        }

        .action-btn a {
            background-color: #4CAF50;
            /* Green */
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            color: white;
        }

        .action-btn a.delete {
            background-color: #f44336;
            /* Red */
        }

        .action-btn a:hover {
            opacity: 0.8;
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

    <!-- Main content -->
    <div class="content">
        <h3>Produk Tersedia</h3>

        <!-- Button to add product -->
        <a href="tambah_produk.php" class="btn">Tambah Produk</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Aksi</th> <!-- Tambahkan kolom aksi -->
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $formattedHarga = number_format($row['Harga'], 0, ',', '.');
                        echo "<tr>
                                <td>{$row['IdProduk']}</td>
                                <td>{$row['NamaProduk']}</td>
                                <td>{$row['Deskripsi']}</td>
                                <td>{$formattedHarga}</td>
                                <td>{$row['Stok']}</td>
                                <td>{$row['NamaKategori']}</td>
                                <td class='action-btn'>
                                    <a href='edit_produk.php?id={$row['IdProduk']}'>Edit</a>
                                    <a href='hapus_produk.php?id={$row['IdProduk']}' class='delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus produk ini?\")'>Hapus</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada produk yang tersedia</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>