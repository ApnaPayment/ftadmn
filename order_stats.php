<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

// ✅ Allow only order admins
if ($_SESSION['role'] !== 'orders') {
    header("Location: admin_dashboard.php");
    exit;
}

// ✅ Fetch data
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$delivered   = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn();
$pending     = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$processing  = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='processing'")->fetchColumn();
$returns     = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='returned'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Statistics</title>
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
    <h2>📦 Order Statistics</h2>

    <div class="charts">
        <!-- Pie Chart -->
        <div class="chart-box">
            <h4>Order Distribution</h4>
            <canvas id="orderPieChart" width="200" height="200"></canvas>
        </div>

        <!-- Bar Chart -->
        <div class="chart-box">
            <h4>Orders Overview</h4>
            <canvas id="orderBarChart" width="400" height="200"></canvas>
        </div>
    </div>

    <p><b>Total Orders:</b> <?= $totalOrders ?></p>
    <p><b>Delivered:</b> <?= $delivered ?></p>
    <p><b>Pending:</b> <?= $pending ?></p>
    <p><b>Processing:</b> <?= $processing ?></p>
    <p><b>Returns:</b> <?= $returns ?></p>

    <button onclick="window.history.back();" class="back-btn">⬅ Go Back</button>

    <script>
    // ✅ Pie Chart (small, radius ~ 2.5cm ≈ 100px)
    new Chart(document.getElementById('orderPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Delivered', 'Pending', 'Processing', 'Returns'],
            datasets: [{
                data: [<?= $delivered ?>, <?= $pending ?>, <?= $processing ?>, <?= $returns ?>],
                backgroundColor: ['#28a745', '#ffc107', '#007bff', '#dc3545']
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // ✅ Bar Chart
    new Chart(document.getElementById('orderBarChart'), {
        type: 'bar',
        data: {
            labels: ['Delivered', 'Pending', 'Processing', 'Returns'],
            datasets: [{
                label: 'Orders',
                data: [<?= $delivered ?>, <?= $pending ?>, <?= $processing ?>, <?= $returns ?>],
                backgroundColor: ['#28a745', '#ffc107', '#007bff', '#dc3545']
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
