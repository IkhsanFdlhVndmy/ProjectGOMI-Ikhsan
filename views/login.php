<?php
session_start();
include "../config.php";


if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Login admin
    if ($email === "admin@gmail.com" && $password === "admin123") {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit();
    }
    // Login user
    $sql = "SELECT * FROM data_user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nama'];
        $_SESSION['user_point'] = $user['point'];
        header("Location: user.php");
        exit();
    } else {
        $error = "Email atau password salah!";
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
        <h1>Login</h1>
        <?php if (isset($error)) { echo "<div class='alert'>$error</div>"; } ?>
        <form method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button class="button" type="submit">Sign In</button>
        </form>
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</div>
</body>
</html>
