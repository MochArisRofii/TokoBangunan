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

// Query untuk mengambil data kategori produk
$sqlKategori = "SELECT * FROM kategori";
$resultKategori = $conn->query($sqlKategori);

// Proses saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaProduk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi']; // Deskripsi bisa kosong
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $idKategori = $_POST['id_kategori'];

    // Validasi input (deskripsi tidak wajib diisi)
    if (empty($namaProduk) || empty($harga) || empty($stok) || empty($idKategori)) {
        $error = "Nama produk, harga, stok, dan kategori harus diisi!";
    } else {
        // Query untuk menyimpan produk baru
        $sqlInsert = "
            INSERT INTO produk (NamaProduk, Deskripsi, Harga, Stok, IdKategori)
            VALUES ('$namaProduk', '$deskripsi', '$harga', '$stok', '$idKategori')
        ";

        if ($conn->query($sqlInsert) === TRUE) {
            // Jika berhasil, arahkan ke halaman produk.php
            header("Location: produk.php"); // Arahkan ke halaman produk.php
            exit();
        } else {
            $error = "Terjadi kesalahan: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Toko Bangunan</title>
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
            font-size: 20px;
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
            padding: 20px;
            width: calc(100% - 220px);
        }

        h3 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #5a67d8;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #4a5ab8;
        }

        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .error {
            color: red;
        }

        .success {
            color: green;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <div class="navbar">
        <h1>Toko Bangunan</h1>
        <a href="index.php">Home</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="lihat_transaksi.php">Lihat Transaksi</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main content -->
    <div class="content">
        <h3>Tambah Produk Baru</h3>

        <!-- Display message if any -->
        <?php if (isset($error)) { ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php } ?>
        <?php if (isset($success)) { ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php } ?>

        <!-- Form untuk tambah produk -->
        <form method="POST" action="tambah_produk.php">
            <label for="nama_produk">Nama Produk</label>
            <input type="text" id="nama_produk" name="nama_produk" required>

            <label for="deskripsi">Deskripsi</label>
            <input type="text" id="deskripsi" name="deskripsi"> <!-- Tidak wajib diisi -->

            <label for="harga">Harga</label>
            <input type="number" id="harga" name="harga" required>

            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" required>

            <label for="id_kategori">Kategori</label>
            <select id="id_kategori" name="id_kategori" required>
                <option value="">Pilih Kategori</option>
                <?php
                if ($resultKategori->num_rows > 0) {
                    while ($row = $resultKategori->fetch_assoc()) {
                        echo "<option value='{$row['IdKategori']}'>{$row['NamaKategori']}</option>";
                    }
                }
                ?>
            </select>

            <button type="submit">Tambah Produk</button>
        </form>
    </div>
</body>

</html>
