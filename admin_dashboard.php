<?php
require_once __DIR__ . "/includes/auth.php";
require_login();

// ✅ User stats
$totalUsers    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers   = $pdo->query("SELECT COUNT(*) FROM users WHERE is_verified=1")->fetchColumn();
$inactiveUsers = $totalUsers - $activeUsers;
$messages      = $pdo->query("SELECT COUNT(*) FROM contact_queries")->fetchColumn();

// ✅ Order stats
$totalOrders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$delivered     = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn();
$pending       = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$processing    = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='processing'")->fetchColumn();
$returns       = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='returned'")->fetchColumn();
$shipped       = $pdo->query("SELECT COUNT(*) FROM orders WHERE status='shipped'")->fetchColumn();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- ✅ use your dark theme CSS -->
    <style>
        /* ✅ Keep only dashboard-specific tweaks */
        h2 { margin-bottom: 20px; display:flex; justify-content:space-between; align-items:center; }

        /* ✅ Cards grid layout */
        .grid { 
            display: flex; 
            flex-wrap: wrap; 
            gap: 20px; 
            margin-bottom: 40px; 
        }
        .card { 
            flex: 1 1 220px; 
            max-width: 250px; 
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 12px; 
            text-align: center; 
            transition: transform 0.25s ease, box-shadow 0.25s ease; 
            color: var(--text);
            background: var(--panel); /* ✅ dark panel */
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .card:hover { 
            transform: translateY(-6px) scale(1.05); 
            box-shadow: 0 10px 24px rgba(0,0,0,0.5); 
        }
        .card h2 { margin: 0; font-size: 2rem; }

        /* ✅ color overrides using theme vars */
        .blue { background: var(--brand); }
        .green { background: var(--ok); }
        .yellow { background: var(--warn); color:#0b1020; }
        .red { background: var(--danger); }
        .orange { background:#fd7e14; }
        .teal { background:#20c997; }

        /* ✅ Charts layout */
        .charts { display: flex; flex-wrap: wrap; gap: 40px; align-items: center; }
        .chart-box { flex: 1; text-align: center; min-width: 300px; }
        canvas { display: block; margin: 0 auto; }

        /* ✅ Back button */
        .back-btn { padding: 10px 20px; border: none; background:var(--brand); color:white; border-radius:8px; cursor:pointer; }
        .back-btn:hover { background:#3a66cc; }
    </style>
</head>
<body>
<div class="container no-sidebar">
  <main class="main">
    <h2>
        📊 Admin Dashboard 
        <button onclick="window.history.back();" class="back-btn">⬅ Go Back</button>
    </h2>

    <?php if ($_SESSION['role'] === 'user'): ?>
        <!-- USER ADMIN VIEW -->
        <div class="grid">
            <div class="card blue">
                <h2><?= $totalUsers ?></h2>
                <p>Total Users 👥</p>
            </div>
            <div class="card green">
                <h2><?= $activeUsers ?></h2>
                <p>Active Users ✅</p>
            </div>
            <div class="card red">
                <h2><?= $inactiveUsers ?></h2>
                <p>Inactive Users ❌</p>
            </div>
            <div class="card yellow">
                <h2><?= $messages ?></h2>
                <p>Messages ✉️</p>
            </div>
        </div>

        <div class="charts">
            <div class="chart-box">
                <h4>User Distribution</h4>
                <canvas id="userPieChart" width="300" height="300"></canvas>
            </div>
            <div class="chart-box">
                <h4>Users Overview</h4>
                <canvas id="userBarChart" width="400" height="250"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'orders'): ?>
        <!-- ORDER ADMIN VIEW -->
        <div class="grid">
            <div class="card yellow">
                <h2><?= $totalOrders ?></h2>
                <p>Total Orders 📦</p>
            </div>
            <div class="card green">
                <h2><?= $delivered ?></h2>
                <p>Delivered ✅</p>
            </div>
            <div class="card teal">
                <h2><?= $shipped ?></h2>
                <p>Shipped 🚚</p>
            </div>
            <div class="card orange">
                <h2><?= $processing ?></h2>
                <p>Processing ⚙️</p>
            </div>
            <div class="card blue">
                <h2><?= $pending ?></h2>
                <p>Pending ⏳</p>
            </div>
            <div class="card red">
                <h2><?= $returns ?></h2>
                <p>Returns 🔄</p>
            </div>
        </div>

        <div class="charts">
            <div class="chart-box">
                <h4>Order Distribution</h4>
                <canvas id="orderPieChart" width="300" height="300"></canvas>
            </div>
            <div class="chart-box">
                <h4>Orders Overview</h4>
                <canvas id="orderBarChart" width="400" height="250"></canvas>
            </div>
        </div>
    <?php endif; ?>
  </main>
</div>

<script>
<?php if ($_SESSION['role'] === 'user'): ?>
// ✅ User Doughnut Chart
new Chart(document.getElementById('userPieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Active Users', 'Inactive Users'],
        datasets: [{
            data: [<?= $activeUsers ?>, <?= $inactiveUsers ?>],
            backgroundColor: ['#22c55e', '#ef4444']
        }]
    },
    options: { responsive: false, plugins: { legend: { position: 'bottom' } } }
});

// ✅ User Bar Chart
new Chart(document.getElementById('userBarChart'), {
    type: 'bar',
    data: {
        labels: ['Total Users', 'Active', 'Inactive', 'Messages'],
        datasets: [{
            label: 'Count',
            data: [<?= $totalUsers ?>, <?= $activeUsers ?>, <?= $inactiveUsers ?>, <?= $messages ?>],
            backgroundColor: ['#5b8cff', '#22c55e', '#ef4444', '#f59e0b']
        }]
    },
    options: { responsive: false, scales: { y: { beginAtZero: true } } }
});
<?php endif; ?>

<?php if ($_SESSION['role'] === 'orders'): ?>
// ✅ Order Doughnut Chart
new Chart(document.getElementById('orderPieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Delivered', 'Shipped', 'Processing', 'Pending', 'Returns'],
        datasets: [{
            data: [<?= $delivered ?>, <?= $shipped ?>, <?= $processing ?>, <?= $pending ?>, <?= $returns ?>],
            backgroundColor: ['#22c55e', '#20c997', '#fd7e14', '#5b8cff', '#ef4444']
        }]
    },
    options: { responsive: false, plugins: { legend: { position: 'bottom' } } }
});

// ✅ Order Bar Chart
new Chart(document.getElementById('orderBarChart'), {
    type: 'bar',
    data: {
        labels: ['Delivered', 'Shipped', 'Processing', 'Pending', 'Returns'],
        datasets: [{
            label: 'Orders',
            data: [<?= $delivered ?>, <?= $shipped ?>, <?= $processing ?>, <?= $pending ?>, <?= $returns ?>],
            backgroundColor: ['#22c55e', '#20c997', '#fd7e14', '#5b8cff', '#ef4444']
        }]
    },
    options: { responsive: false, scales: { y: { beginAtZero: true } } }
});
<?php endif; ?>
</script>
</body>
</html>
