<?php
include "../config.php";

// Pastikan hanya admin yang bisa mengakses
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../login.php");
    exit();
}

// Proses input data sampah
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']) && $_POST['action'] == "submit_data") {
    $response = ['success' => false, 'message' => 'Terjadi kesalahan!'];

    if (!empty($_POST['user_id']) && !empty($_POST['kategori']) && !empty($_POST['berat'])) {
        $user_id = intval($_POST['user_id']);
        $kategori = $_POST['kategori'];
        $berat_kg = floatval($_POST['berat']);

        // Fetch poin per kg sesuai kategori
        $poin_sql = "SELECT poin_per_kg FROM data_sampah WHERE kategori_sampah = ?";
        $stmt = $conn->prepare($poin_sql);
        $stmt->bind_param("s", $kategori);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            $poin_per_kg = $data['poin_per_kg'];
            $total_poin = $poin_per_kg * $berat_kg;

            // Update data user
            $update_user_sql = "UPDATE data_user SET point = point + ?, sampah_terkumpul = sampah_terkumpul + ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_user_sql);
            $update_stmt->bind_param("dii", $total_poin, $berat_kg, $user_id);
            $update_stmt->execute();

            // Insert ke tabel transaksi_sampah
            $insert_transaksi_sql = "INSERT INTO transaksi_sampah (user_id, sampah_id, berat_kg, poin_ditambahkan) 
                                     VALUES (?, (SELECT id FROM data_sampah WHERE kategori_sampah = ?), ?, ?)";
            $insert_stmt = $conn->prepare($insert_transaksi_sql);
            $insert_stmt->bind_param("isid", $user_id, $kategori, $berat_kg, $total_poin);
            $insert_stmt->execute();

            $response = ['success' => true, 'message' => 'Data berhasil ditambahkan!'];
        } else {
            $response['message'] = 'Kategori sampah tidak ditemukan!';
        }
    } else {
        $response['message'] = 'Data tidak lengkap!';
    }

    echo json_encode($response);
    exit();
}

// Proses Searching User
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']) && $_POST['action'] == "search_user") {
    $query = $_POST['query'] ?? '';

    if (!empty($query)) {
        $sql = "SELECT id, nama, email FROM data_user WHERE nama LIKE ? OR email LIKE ? LIMIT 5";
        $stmt = $conn->prepare($sql);
        $search = "%$query%";
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<div onclick='selectUser({$row['id']}, \"{$row['nama']} - {$row['email']}\")'>{$row['nama']} ({$row['email']})</div>";
        }
    } else {
        echo "Query kosong.";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../assets/admin.style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <title>Admin - GOMI</title>
</head>
<body>
<div class="navbar">
    <h1>GOMI Admin Panel</h1>
    <a href="../logout.php">Logout</a>
</div>

<div class="container">
    <h2>Hi Admin, Welcome to GOMI</h2>
    <p>Pandai Mengelola Sampah dengan Bijak</p>

    <h2>Pilih Kategori Sampah</h2>
    <div class="categories">
        <div class="card" onclick="openModal('Plastik')">
            <img src="../assets/plastik.png" alt="Sampah Plastik">
            <p>Sampah Plastik</p>
        </div>
        <div class="card" onclick="openModal('Kaleng')">
            <img src="../assets/kaleng.png" alt="Sampah Kaleng">
            <p>Sampah Kaleng</p>
        </div>
        <div class="card" onclick="openModal('Kaca')">
            <img src="../assets/kaca.png" alt="Sampah Kaca">
            <p>Sampah Kaca</p>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Input Data Sampah: <span id="modal-kategori"></span></h3>
            <form id="data-form">
                <input type="hidden" name="action" value="submit_data">
                <input type="hidden" name="kategori" id="kategori">
                <div>
                    <label>User</label>
                    <input type="text" id="search" placeholder="Cari nama atau email" onkeyup="searchUser()">
                    <div id="user-list"></div>
                    <input type="hidden" name="user_id" id="user_id">
                </div>
                <div>
                    <label>Berat Sampah (kg)</label>
                    <input type="range" min="1" max="100" step="0.1" id="weight" name="berat" oninput="updateWeight()">
                    <span id="weight-value">1 kg</span>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </div>
</div>

<script>
function openModal(kategori) {
    document.getElementById('modal').style.display = 'flex';
    document.getElementById('kategori').value = kategori;
    document.getElementById('modal-kategori').textContent = kategori;

    // Reset form
    document.getElementById('search').value = '';
    document.getElementById('user_id').value = '';
    document.getElementById('weight').value = 1;
    document.getElementById('weight-value').textContent = '1 kg';
    document.getElementById('user-list').innerHTML = '';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}

function updateWeight() {
    const weight = document.getElementById('weight').value;
    document.getElementById('weight-value').textContent = weight + ' kg';
}

function searchUser() {
    const query = document.getElementById('search').value;
    if (query.length > 2) {
        $.ajax({
            url: '', // File ini
            type: 'POST',
            data: { action: 'search_user', query: query },
            success: function(data) {
                $('#user-list').html(data);
            }
        });
    } else {
        $('#user-list').html('');
    }
}

function selectUser(id, detail) {
    document.getElementById('search').value = detail;
    document.getElementById('user_id').value = id;
    document.getElementById('user-list').innerHTML = '';
}

$('#data-form').on('submit', function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    $.ajax({
        url: '', // File ini
        type: 'POST',
        data: formData,
        success: function(response) {
            const res = JSON.parse(response);
            alert(res.message);
            if (res.success) {
                closeModal();
            }
        }
    });
});
</script>
</body>
</html>
