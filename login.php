<?php
session_start();
include('koneksi.php'); // Memasukkan konfigurasi database

// Cek apakah pengguna sudah login
if (isset($_SESSION['IdUser'])) {
    header("Location: index.php"); // Arahkan ke halaman index jika sudah login
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Query untuk memeriksa apakah username dan password cocok
    $sql = "SELECT * FROM users WHERE Username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Memeriksa apakah password cocok dengan plain text
        if ($password === $user['Password']) {
            $_SESSION['IdUser'] = $user['IdUser']; // Simpan ID pengguna di session
            $_SESSION['Username'] = $user['Username']; // Simpan Username
            $_SESSION['Nama'] = $user['Nama']; // Simpan Nama pengguna

            // Redirect ke halaman index setelah login sukses
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Username atau Password salah!";
        }
    } else {
        $error_message = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Toko Bangunan</title>
    <!-- <link rel="stylesheet" href="css/style.css"> -->
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        .login-container p {
            color: red;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #5a67d8;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #4c51bf;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if (isset($error_message)) {
            echo "<p>$error_message</p>";
        } ?>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>

</html>