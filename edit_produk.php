<?php
session_start();
include('koneksi.php');

// Cek apakah pengguna sudah login
if (!isset($_SESSION['IdUser'])) {
    header("Location: login.php"); // Jika belum login, arahkan ke halaman login
    exit();
}

if (isset($_GET['id'])) {
    $idProduk = $_GET['id'];
    $sqlProduk = "SELECT * FROM produk WHERE IdProduk = '$idProduk'";
    $resultProduk = $conn->query($sqlProduk);

    // Periksa apakah query berhasil dan produk ditemukan
    if ($resultProduk->num_rows > 0) {
        $produk = $resultProduk->fetch_assoc();
    } else {
        die("Produk tidak ditemukan.");
    }

    // Proses update produk
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $namaProduk = $_POST['nama_produk'];
        $deskripsi = $_POST['deskripsi'];
        $harga = $_POST['harga'];
        $stok = $_POST['stok'];
        $idKategori = $_POST['id_kategori'];

        // Query untuk memperbarui data produk
        $sqlUpdate = "
            UPDATE produk
            SET NamaProduk = '$namaProduk', Deskripsi = '$deskripsi', Harga = '$harga', Stok = '$stok', IdKategori = '$idKategori'
            WHERE IdProduk = '$idProduk'
        ";

        if ($conn->query($sqlUpdate) === TRUE) {
            header("Location: produk.php"); // Arahkan ke halaman produk setelah berhasil
            exit();
        } else {
            $error = "Terjadi kesalahan: " . $conn->error;
        }
    }
} else {
    die("ID produk tidak ditemukan.");
}

// Ambil data kategori untuk dropdown
$sqlKategori = "SELECT * FROM kategori";
$resultKategori = $conn->query($sqlKategori);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <title>Edit Produk</title>
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
            margin-bottom: 30px;
        }

        form {
            width: 50%;
            /* Lebar form lebih besar untuk tampilan lebih leluasa */
            margin: 20px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* Styling untuk label */
        form label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        /* Styling untuk input dan select */
        input,
        select {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            /* Jarak antar elemen input */
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        /* Efek saat input/ select difokuskan */
        input:focus,
        select:focus {
            border-color: #5a67d8;
            background-color: #fff;
        }

        /* Styling untuk button submit */
        button {
            padding: 12px;
            background-color: #4a67d8;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #3b56b8;
        }

        /* Styling untuk select kategori */
        select {
            -webkit-appearance: none;
            /* Menghilangkan default arrow pada select di beberapa browser */
            -moz-appearance: none;
            appearance: none;
            background-image: url('https://img.icons8.com/ios-filled/50/000000/chevron-down.png');
            /* Ikon dropdown */
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 20px;
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

    <!-- Main content -->
    <div class="content">
        <h3>Edit Produk</h3>

        <!-- Form untuk Edit Produk -->
        <form method="POST" action="edit_produk.php?id=<?php echo $produk['IdProduk']; ?>">
            <label for="nama_produk">Nama Produk</label>
            <input type="text" id="nama_produk" name="nama_produk" value="<?php echo $produk['NamaProduk']; ?>"
                required>

            <label for="deskripsi">Deskripsi</label>
            <input type="text" id="deskripsi" name="deskripsi" value="<?php echo $produk['Deskripsi']; ?>">

            <label for="harga">Harga</label>
            <input type="number" id="harga" name="harga" value="<?php echo $produk['Harga']; ?>" required>

            <label for="stok">Stok</label>
            <input type="number" id="stok" name="stok" value="<?php echo $produk['Stok']; ?>" required>

            <label for="id_kategori">Kategori</label>
            <select id="id_kategori" name="id_kategori" required>
                <option value="">Pilih Kategori</option>
                <?php
                if ($resultKategori->num_rows > 0) {
                    while ($row = $resultKategori->fetch_assoc()) {
                        $selected = $row['IdKategori'] == $produk['IdKategori'] ? 'selected' : '';
                        echo "<option value='{$row['IdKategori']}' $selected>{$row['NamaKategori']}</option>";
                    }
                }
                ?>
            </select>

            <button type="submit">Update Produk</button>
        </form>
    </div>
</body>

</html>