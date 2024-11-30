<?php
include "../config.php";
include "../session.php";

// Pastikan session dimulai

// Ambil user_id dari session
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Ambil data user berdasarkan sesi aktif
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM data_user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Ambil data JSON dari AJAX
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data) {
        $type = $data['type']; // 'uang' atau 'hadiah'
        $value = intval($data['value']);
        $points_needed = intval($data['points_needed']);

        if ($user['point'] >= $points_needed) {
            $new_points = $user['point'] - $points_needed;

            // Update poin di database
            $update_sql = "UPDATE data_user SET point = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $new_points, $user_id);
            $update_stmt->execute();

            // Insert ke log transaksi
            $log_sql = "INSERT INTO transaksi (user_id, type, value, points_used) VALUES (?, ?, ?, ?)";
            $log_stmt = $conn->prepare($log_sql);
            $log_stmt->bind_param("isii", $user_id, $type, $value, $points_needed);
            $log_stmt->execute();

            // Kirim response JSON sukses
            echo json_encode([
                "success" => true,
                "message" => "Berhasil menukarkan poin!",
                "new_points" => $new_points
            ]);
        } else {
            // Kirim response JSON gagal
            echo json_encode([
                "success" => false,
                "message" => "Poin tidak mencukupi untuk pertukaran ini."
            ]);
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../assets/style.css">
    <title>User Dashboard - Gomi</title>
</head>
<body>
<div class="container">
    <div class="card">
        <h1>Hi, <?= htmlspecialchars($user['nama']) ?>! Welcome to Gomi</h1>
        <p>Pandai Mengelola Sampah dengan Bijak</p>

        <div class="point-section">
            <h3>My Point: <?= number_format($user['point'], 0) ?></h3>
            <p>Sampah Terkumpul :<?= htmlspecialchars($user['sampah_terkumpul'] ?? 0) ?> KG. </p>
        </div>

        <h2>Tukarkan Point Dengan Uang</h2>
        <div class="exchange-grid">
            <form method="POST" onsubmit="return tukarPoin(event, 'uang', 50000, 100); ">
                <button class="button">Rp50.000 (100 Poin)</button>
            </form>
            <form method="POST" onsubmit="return tukarPoin(event, 'uang', 100000, 180); ">
                <button class="button">Rp100.000 (180 Poin)</button>
            </form>
            <form method="POST" onsubmit="return tukarPoin(event, 'uang', 150000, 250);">
                <button class="button">Rp150.000 (250 Poin)</button>
            </form>
        </div>

        <h2>Tukarkan Point Dengan Hadiah</h2>
        <div class="exchange-grid">
            <form method="POST" onsubmit="return tukarPoin(event, 'hadiah', 50000, 100); ">
                <button class="button">Voucher Game (100 Poin)</button>
            </form>
            <form method="POST" onsubmit="return tukarPoin(event, 'hadiah', 100000, 150) ;">
                <button class="button">Voucher Belanja (150 Poin)</button>
            </form>
            <form method="POST" onsubmit="return tukarPoin(event, 'hadiah', 150000, 200) ;">
                <button class="button">Voucher Diskon (200 Poin)</button>
            </form>
        </div>
    </div>

    <!-- Logout Dropdown -->
    <div class="dropdown">
        <button class="dropdown-button"><?= htmlspecialchars($user['nama']) ?></button>
        <div class="dropdown-content">
            <a href="../logout.php">Logout</a>
        </div>
    </div>
</div>

<script>
    function tukarPoin(event, type, value, pointsNeeded) {
        event.preventDefault(); // Mencegah reload halaman

        // Kirim data ke server menggunakan fetch API
        fetch("user.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                type: type,
                value: value,
                points_needed: pointsNeeded,
            }),
        })
        .then(response => response.json()) // Parsing response JSON dari server
        .then(data => {
            if (data.success) {
                alert(data.message); // Tampilkan alert
                // Perbarui tampilan poin di halaman
                document.querySelector(".point-section h3").textContent = `My Point: ${data.new_points.toLocaleString()}`;
            } else {
                alert(data.message); // Tampilkan pesan error jika ada
            }
        })
        .catch(error => console.error("Error:", error));
    }
</script>
</body>
</html>