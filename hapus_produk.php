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

    // Query untuk menghapus produk
    $sqlHapus = "DELETE FROM produk WHERE IdProduk = '$idProduk'";

    if ($conn->query($sqlHapus) === TRUE) {
        header("Location: produk.php"); // Arahkan ke halaman produk setelah berhasil
        exit();
    } else {
        die("Terjadi kesalahan: " . $conn->error);
    }
} else {
    die("ID produk tidak ditemukan.");
}
?>
