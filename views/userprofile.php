<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include "../config.php";
include "../session.php";

// Pastikan session dimulai

// Ambil user_id dari session
if (!isset($_SESSION['user_id'])) {
    header("Location: signin-signup.php");
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

if (isset($_GET['action']) && $_GET['action'] === 'get_graph_data') {
  header('Content-Type: application/json');

  $sql = "SELECT DATE(created_at) as date, SUM(berat_kg) as total_kg 
          FROM transaksi_sampah 
          WHERE user_id = ? 
          GROUP BY DATE(created_at) 
          ORDER BY DATE(created_at) DESC 
          LIMIT 7";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  $data = [];
  while ($row = $result->fetch_assoc()) {
      $data[] = [
          'date' => $row['date'],
          'total_kg' => floatval($row['total_kg']),
      ];
  }
  echo json_encode($data);
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Gomi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .dashboard-header {
            background-color: #ffffff;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
        }
        .button:hover {
            background-color: #0056b3;
        }
        .dropdown-menu {
        border-radius: 5px;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #000;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        
        <!-- Content -->
        <div class="col-md-12">
          <div class="dashboard-header d-flex justify-content-between align-items-center">
              <h1>Hello, <?= htmlspecialchars($user['nama']) ?>!</h1>
              <div class="dropdown">
                  <img 
                      src="https://via.placeholder.com/50" 
                      alt="User Avatar" 
                      class="rounded-circle dropdown-toggle" 
                      id="avatarDropdown" 
                      data-bs-toggle="dropdown" 
                      aria-expanded="false"
                      style="cursor: pointer;">
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="avatarDropdown">
                      <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                  </ul>
              </div>
          </div>
            <div class="container mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card p-4">
                            <h4>Point Saya:</h4>
                            <h2><?= number_format($user['point'], 0) ?></h2>
                            <button class="button">Tukarkan Point</button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-5">
                            <h4>Sampah Terkumpul:</h4>
                            <h2><?= htmlspecialchars($user['sampah_terkumpul'] ?? 0) ?> KG</h2>
                        </div>
                    </div>
                </div>
                <div class="card mt-4 p-4">
                    <h4>Grafik Transaksi Anda:</h4>
                    <div id="lineGraph"></div>
                </div>
            </div>
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

    document.addEventListener('DOMContentLoaded', function () {
    fetch("user.php?action=get_graph_data")
        .then(response => response.json())
        .then(data => {
            const dates = data.map(item => item.date).reverse(); // Urutkan tanggal dari lama ke baru
            const weights = data.map(item => item.total_kg).reverse();

            const options = {
                chart: {
                    type: 'line',
                    height: 350,
                },
                series: [{
                    name: 'Berat Sampah (kg)',
                    data: weights,
                }],
                xaxis: {
                    categories: dates,
                    title: {
                        text: 'Tanggal',
                    },
                },
                yaxis: {
                    title: {
                        text: 'Berat Sampah (kg)',
                    },
                },
                colors: ['#00BAEC'],
                
            };

            const chart = new ApexCharts(document.querySelector("#lineGraph"), options);
            chart.render();
        })
        .catch(error => console.error('Error fetching graph data:', error));
});




</script>
</body>
</html>