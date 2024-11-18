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
    <title>Edit Produk</title>
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
            /* Offset for the navbar */
            padding: 20px;
            width: calc(100% - 220px);
        }

        h3 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
        }

        input,
        select {
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

        /* Table styling */
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