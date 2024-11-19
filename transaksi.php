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
    <style>
        /* Gaya halaman */
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
            margin: 5px 0;
            display: block;
        }

        .navbar a:hover {
            background-color: #333;
        }

        .content {
            margin-left: 220px;
            padding: 20px;
            width: calc(100% - 220px);
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
    </style>
</head>

<body>
    <div class="navbar">
        <h1>Toko Bangunan</h1>
        <a href="index.php">Home</a>

        <a href="produk.php">Produk</a>
        <a href="transaksi.php">Transaksi</a>
        <a href="lihat_transaksi.php">Lihat Transaksi</a>
        <a href="laporan.php">Laporan</a>
        <a href="logout.php">Logout</a>
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
                                <form action='' method='POST'>
                                    <input type='hidden' name='IdProduk' value='{$row['IdProduk']}'>
                                    <input type='hidden' name='NamaProduk' value='{$row['NamaProduk']}'>
                                    <input type='hidden' name='Harga' value='{$row['Harga']}'>
                                    <input type='number' name='Jumlah' placeholder='Jumlah' required min='1' max='{$row['Stok']}'>
                                    <button type='submit' name='TambahKeKeranjang'>Tambah</button>
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
                                <form method='POST'>
                                    <input type='hidden' name='hapusIndex' value='{$index}'>
                                    <button type='submit' name='HapusDariKeranjang'>Hapus</button>
                                </form>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Keranjang kosong</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <form method="POST">
            <button type="submit" name="SimpanTransaksi">Simpan Transaksi</button>
        </form>
    </div>
</body>

</html>