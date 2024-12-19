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
            $_SESSION['NamaUsers'] = $user['NamaUsers']; // Simpan Nama Users
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
    <link rel="stylesheet" href="fontawesome-free-6.7.2-web/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Basic reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            /* background: linear-gradient(to right, #6a11cb, #2575fc); */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .login-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            /* Mempertebal shadow */
            width: 100%;
            max-width: 400px;
        }

        .login-container p {
            color: red;
            text-align: center;
            font-size: 14px;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-size: 16px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #6a11cb;
            outline: none;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background-color: #6a11cb;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #2575fc;
        }

        .social-login {
            text-align: center;
            margin-top: 20px;
        }

        .social-login a {
            margin: 0 10px;
            padding: 10px 15px;
            background-color: #3b5998;
            color: #fff;
            text-decoration: none;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .social-login a:hover {
            background-color: #8b9dc3;
        }

        .forgot-password {
            display: block;
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            color: #2575fc;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .password-container {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 35%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            /* Mengatur ukuran ikon mata */
            color: #888;
            /* Memberikan warna abu-abu pada ikon */
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: #6a11cb;
            /* Ubah warna saat hover */
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
            <label for="username"><i class="fa-solid fa-user"></i> Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password"><i class="fa-solid fa-key"></i> Password:</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <i class="fa-solid fa-eye-slash toggle-password" id="toggle-password"></i>
            </div>

            <input type="submit" value="Login">
        </form>

    </div>

    <script>
        // Menambahkan event listener untuk toggle password
        const togglePassword = document.getElementById('toggle-password');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            // Toggle antara tipe password dan text
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Ubah ikon mata menjadi dicoret atau tidak
            this.classList.toggle('fa-eye'); // Ganti ikon mata ke normal
            this.classList.toggle('fa-eye-slash'); // Ganti ikon mata ke dicoret
        });
    </script>

</body>

</html>