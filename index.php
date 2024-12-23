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

// Nonaktifkan ONLY_FULL_GROUP_BY (opsional, jika diperlukan)
$conn->query("SET SESSION sql_mode = ''");

// Ambil data produk dan stok produk dari database
$queryProduk = "SELECT NamaProduk, SUM(Stok) AS JumlahStok FROM produk GROUP BY NamaProduk";
$resultProduk = $conn->query($queryProduk);

// Simpan data produk dan stoknya untuk grafik
$produk = [];
$stok = [];
if ($resultProduk) {
    while ($row = $resultProduk->fetch_assoc()) {
        $produk[] = $row['NamaProduk'];
        $stok[] = $row['JumlahStok'];
    }
} else {
    echo "Error: " . $conn->error;
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Toko Bangunan</title>
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            transition: background-color 0.3s, color 0.3s;
        }

        body.dark-mode {
            background-color: #1a202c;
            color: #e2e8f0;
        }

        .navbar.dark-mode {
            background: linear-gradient(135deg, #2d3748, #4a5568);
        }

        .dark-mode .section {
            background-color: #2d3748;
            color: #e2e8f0;
        }

        .dark-mode .section h2,
        .dark-mode .section h3,
        .dark-mode table td {
            color: #e2e8f0;
        }

        .dark-mode table th {
            background-color: rgb(31, 31, 31);
            color: #e2e8f0;
        }

        .toggle-button {
            padding: 12px 16px;
            background-color: #4a67d8;
            color: #fff;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            font-size: 22px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin-left: auto;
        }

        .toggle-button:hover {
            background-color: #5a80f0;
            transform: scale(1.1);
        }

        .toggle-button i {
            transition: transform 0.3s ease;
        }

        .toggle-button.dark-mode i {
            transform: rotate(180deg);
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

        /* Konten utama */
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        h2 {
            color: #4a67d8;
            margin-bottom: 20px;
        }

        .section {
            margin-bottom: 40px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .section h3 {
            color: #333;
            margin-bottom: 20px;
        }

        /* Grafik */
        .chart-container {
            margin: 0 auto;
            width: 100%;
            max-width: 800px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            text-align: left;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
        }

        table th {
            background-color: #4a67d8;
            color: #fff;
            font-weight: bold;
        }

        table td {
            color: #555;
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
            align-items: center;
            justify-content: flex-start;
            height: 100%;
            padding-top: 20px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-top: 20px;
        }

        .user-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
            object-fit: cover;
            border: 2px solid #fff;
        }

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
        <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <h1>Toko Bangunan</h1>
            <!-- Tombol Dark Mode -->
            <button class="toggle-button" onclick="toggleDarkMode()">
                <i id="mode-icon" class="fa-solid fa-sun"></i>
            </button>
        </div>
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

    <!-- Konten utama -->
    <div class="main-content">
        <div class="section">
            <h2>Selamat datang, <?php echo htmlspecialchars($nama); ?></h2>
            <h3>Grafik Stok Produk</h3>

            <!-- Grafik Stok Produk -->
            <div class="chart-container">
                <canvas id="produkChart"></canvas>
            </div>
        </div>

        <div class="section">
            <h3>Daftar Produk dan Stoknya</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>Jumlah Stok</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produk as $index => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item); ?></td>
                            <td><?php echo $stok[$index]; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const produk = <?php echo json_encode($produk); ?>;
        const stok = <?php echo json_encode($stok); ?>;

        const ctx = document.getElementById('produkChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: produk,
                datasets: [{
                    label: 'Jumlah Stok',
                    data: stok,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <script>
        function loadDarkModePreference() {
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                document.body.classList.add('dark-mode');
                document.querySelector('.navbar').classList.add('dark-mode');
                document.getElementById('mode-icon').classList.remove('fa-sun');
                document.getElementById('mode-icon').classList.add('fa-moon');
            }
        }

        function toggleDarkMode() {
            const body = document.body;
            const navbar = document.querySelector('.navbar');
            const icon = document.getElementById('mode-icon');
            const isDarkMode = body.classList.toggle('dark-mode');
            navbar.classList.toggle('dark-mode');

            // Ubah ikon sesuai mode
            if (isDarkMode) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            } else {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }

            // Simpan preferensi mode ke localStorage
            localStorage.setItem('darkMode', isDarkMode);
        }

        loadDarkModePreference();  // Load preferensi saat halaman dimuat
    </script>
</body>

</html>
