<?php
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Cek apakah email sudah terdaftar
    $check_sql = "SELECT id FROM data_user WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        // Jika email belum terdaftar, lakukan proses insert
        $sql = "INSERT INTO data_user (nama, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nama, $email, $password);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Register</h1>
        <!-- Tampilkan pesan error jika ada -->
        <?php if (isset($error)) { echo "<div class='alert'>$error</div>"; } ?>
        <form method="POST">
            <div class="input-group">
                <label>Nama</label>
                <input type="text" name="nama" required>
            </div>
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button class="button" type="submit">Sign Up</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</div>

<script>
    document.querySelector("form").addEventListener("submit", function (event) {
        const email = document.querySelector("input[name='email']").value;

        if (!email.includes("@")) {
            alert("Masukkan email yang valid.");
            event.preventDefault();
        }
    });
</script>
</body>
</html>
