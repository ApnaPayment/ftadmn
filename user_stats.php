<?php
require_once __DIR__ . "/includes/auth.php";
require_login();
if ($_SESSION['role'] !== 'user') {
    header("Location: admin_dashboard.php"); // or show error
    exit;
}

// ✅ Fetch data
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified=1")->fetchColumn();
$inactiveUsers = $totalUsers - $activeUsers;
$messages = $pdo->query("SELECT COUNT(*) FROM contact_queries")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Statistics</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .charts { display: flex; gap: 40px; align-items: center; }
        .chart-box { text-align: center; }
        canvas { display: block; margin: 0 auto; }
        .back-btn { margin-top: 20px; padding: 10px 20px; border: none; background:#007BFF; color:white; border-radius:5px; cursor:pointer; }
    </style>
</head>
<body>
    <h2>👥 User Statistics</h2>

    <div class="charts">
        <!-- Pie Chart -->
        <div class="chart-box">
            <h4>Active vs Inactive</h4>
            <canvas id="userPieChart" width="200" height="200"></canvas>
        </div>

        <!-- Bar Chart -->
        <div class="chart-box">
            <h4>Users Overview</h4>
            <canvas id="userBarChart" width="400" height="200"></canvas>
        </div>
    </div>

    <p><b>Total Users:</b> <?= $totalUsers ?></p>
    <p><b>Active Users:</b> <?= $activeUsers ?></p>
    <p><b>Inactive Users:</b> <?= $inactiveUsers ?></p>
    <p><b>Messages Sent:</b> <?= $messages ?></p>

    <button onclick="window.history.back();" class="back-btn">⬅ Go Back</button>

    <script>
    // ✅ Pie Chart (small one, radius ~ 2.5cm ≈ 100px)
    new Chart(document.getElementById('userPieChart'), {
        type: 'pie',
        data: {
            labels: ['Active Users', 'Inactive Users'],
            datasets: [{
                data: [<?= $activeUsers ?>, <?= $inactiveUsers ?>],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // ✅ Bar Chart
    new Chart(document.getElementById('userBarChart'), {
        type: 'bar',
        data: {
            labels: ['Total Users', 'Active', 'Inactive', 'Messages'],
            datasets: [{
                label: 'Count',
                data: [<?= $totalUsers ?>, <?= $activeUsers ?>, <?= $inactiveUsers ?>, <?= $messages ?>],
                backgroundColor: ['#007bff', '#28a745', '#dc3545', '#ffc107']
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } }
        }
    });
    </script>
</body>
</html>
