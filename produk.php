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

// Memastikan nama pengguna ada dalam sesi
if (isset($_SESSION['NamaUsers'])) {
    $NamaUsers = $_SESSION['NamaUsers'];
} else {
    // Jika 'Name' tidak ada dalam session, bisa gunakan nama yang sudah ada
    $NamaUsers = $nama;
}
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
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <title>Selamat Datang di Toko Bangunan</title>
    <style>
        /* Basic reset */
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
</head>

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
