<?php
$servername = "localhost";  // Ganti dengan server Anda jika diperlukan
$username = "root";         // Ganti dengan username database Anda
$password = "aris_januari2007";// Ganti dengan password database Anda
$password = "";             // Ganti dengan password database Anda
$dbname = "toko_bangunan";  // Ganti dengan nama database Anda

// Buat koneksi
$conn = new mysqli(
    $servername,
    $username,
    $password,
    $dbname
);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
