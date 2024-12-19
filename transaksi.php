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

// Proses menambahkan ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['TambahKeKeranjang'])) {
    $idProduk = $_POST['IdProduk'];
    $namaProduk = $_POST['NamaProduk'];
    $harga = $_POST['Harga'];
    $jumlah = $_POST['Jumlah'];
    $subtotal = $harga * $jumlah;

    if (!isset($_SESSION['keranjang'])) {
        $_SESSION['keranjang'] = [];
    }

    $_SESSION['keranjang'][] = [
        'IdProduk' => $idProduk,
        'NamaProduk' => $namaProduk,
        'Harga' => $harga,
        'Jumlah' => $jumlah,
        'Subtotal' => $subtotal
    ];
}

// Proses menghapus item dari keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['HapusDariKeranjang'])) {
    $hapusIndex = $_POST['hapusIndex'];

    if (isset($_SESSION['keranjang'][$hapusIndex])) {
        unset($_SESSION['keranjang'][$hapusIndex]);
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    }
}

// Menyimpan transaksi ke database
if (isset($_POST['SimpanTransaksi'])) {
    $idUser = $_SESSION['IdUser'];
    $tanggal = date('Y-m-d H:i:s');
    $totalBayar = array_sum(array_column($_SESSION['keranjang'], 'Subtotal'));

    $stmt = $conn->prepare("INSERT INTO transaksi (IdUser, Tanggal, TotalBayar) VALUES (?, ?, ?)");
    $stmt->bind_param("isd", $idUser, $tanggal, $totalBayar);

    if ($stmt->execute()) {
        $idTransaksi = $stmt->insert_id;

        $stmtDetail = $conn->prepare("INSERT INTO transaksi_item (IdTransaksi, IdProduk, Jumlah, Subtotal) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['keranjang'] as $item) {
            $stmtDetail->bind_param("iiid", $idTransaksi, $item['IdProduk'], $item['Jumlah'], $item['Subtotal']);
            $stmtDetail->execute();
        }

        unset($_SESSION['keranjang']);
        echo "<script>alert('Transaksi berhasil disimpan!');</script>";
    } else {
        echo "<script>alert('Gagal menyimpan transaksi!');</script>";
    }
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
    <title>Transaksi</title>
    <!-- Tambahkan link Font Awesome -->
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Gaya halaman */
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

        .content {
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

        form {
            display: inline;
        }

        button[type="submit"] {
            background-color: #5a67d8;
            color: #fff;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 5px;
        }

        button[type="submit"]:hover {
            background-color: #4a5ab8;
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

    <div class="content">
        <h3>Produk Tersedia</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Produk</th>
                    <th>Deskripsi</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Kategori</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['IdProduk']}</td>
                            <td>{$row['NamaProduk']}</td>
                            <td>{$row['Deskripsi']}</td>
                            <td>" . number_format($row['Harga'], 0, ',', '.') . "</td>
                            <td>{$row['Stok']}</td>
                            <td>{$row['NamaKategori']}</td>
                            <td>
                                <form action='' method='POST' style='display: flex; align-items: center;'>
                                    <input type='hidden' name='IdProduk' value='{$row['IdProduk']}'>
                                    <input type='hidden' name='NamaProduk' value='{$row['NamaProduk']}'>
                                    <input type='hidden' name='Harga' value='{$row['Harga']}'>
                                    <div style='display: flex; align-items: center;'>
                                        <input type='number' name='Jumlah' placeholder='Jumlah' required min='1' max='{$row['Stok']}' 
                                        style='padding: 5px; font-size: 14px; width: 80px; border-radius: 4px; border: 1px solid #ccc; margin-right: 10px;'>
                                        <button type='submit' name='TambahKeKeranjang' 
                                            style='background-color: #5a67d8; color: white; border: none; padding: 6px 15px; font-size: 14px; 
                                            cursor: pointer; border-radius: 4px; transition: background-color 0.3s ease;'>
                                            <i class='fas fa-cart-plus' style='margin-right: 8px;'></i>Tambah
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>Tidak ada produk tersedia</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h3>Keranjang</h3>
        <table>
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($_SESSION['keranjang'])) {
                    foreach ($_SESSION['keranjang'] as $index => $item) {
                        echo "<tr>
                            <td>{$item['NamaProduk']}</td>
                            <td>" . number_format($item['Harga'], 0, ',', '.') . "</td>
                            <td>{$item['Jumlah']}</td>
                            <td>" . number_format($item['Subtotal'], 0, ',', '.') . "</td>
                            <td>
                                <form action='' method='POST'>
                                    <input type='hidden' name='hapusIndex' value='{$index}'>
                                    <button type='submit' name='HapusDariKeranjang' 
                                        style='background-color: #e53e3e; color: white; border: none; padding: 6px 15px; font-size: 14px; cursor: pointer; border-radius: 4px;'>Hapus</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Keranjang Anda kosong</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <?php if (!empty($_SESSION['keranjang'])): ?>
            <form action="" method="POST">
                <button type="submit" name="SimpanTransaksi"
                    style="background-color: #48bb78; color: white; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 5px;">Selesaikan
                    Transaksi</button>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>