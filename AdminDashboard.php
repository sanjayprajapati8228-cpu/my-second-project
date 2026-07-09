<?php
include 'config.php';

// Fetch counts for each category
$doctor_count = $conn->query("SELECT id FROM add_doctor")->num_rows;
$appointment_count = $conn->query("SELECT id FROM user_appointments")->num_rows;
$user_count = $conn->query("SELECT id FROM user_registration1")->num_rows;
$order_count = $conn->query("SELECT id FROM orders")->num_rows;
$pharmacy_count = $conn->query("SELECT id FROM products")->num_rows;
$feedback_count = $conn->query("SELECT id FROM contect_form")->num_rows;

$stats = [
    ['label' => 'Doctors', 'count' => $doctor_count, 'icon' => 'fa-user-md', 'link' => 'FeatchDoctors.php', 'color' => '#0b7fab'],
    ['label' => 'Appointments', 'count' => $appointment_count, 'icon' => 'fa-calendar-check', 'link' => 'FeatchAppointments.php', 'color' => '#198754'],
    ['label' => 'Users', 'count' => $user_count, 'icon' => 'fa-hospital-user', 'link' => 'FeatchUserR.php', 'color' => '#ef7f1a'],
    ['label' => 'Orders', 'count' => $order_count, 'icon' => 'fa-cart-shopping', 'link' => 'admin_orders.php', 'color' => '#6f42c1'],
    ['label' => 'Pharmacy', 'count' => $pharmacy_count, 'icon' => 'fa-pills', 'link' => 'fetch_pharmacy.php', 'color' => '#0aa37f'],
    ['label' => 'Feedback', 'count' => $feedback_count, 'icon' => 'fa-comments', 'link' => 'FetchContect.php', 'color' => '#d63384']
];

$counts = array_column($stats, 'count');
$total_records = array_sum($counts);
$max_count = max(max($counts), 1);
$max_label = 'Doctors';
$max_value = $doctor_count;
foreach ($stats as $item) {
    if ($item['count'] > $max_value) {
        $max_label = $item['label'];
        $max_value = $item['count'];
    }
}

$chart_segments = [];
$chart_start = 0;
if ($total_records > 0) {
    foreach ($stats as $item) {
        $portion = ($item['count'] / $total_records) * 100;
        $chart_end = $chart_start + $portion;
        $chart_segments[] = $item['color'] . ' ' . round($chart_start, 2) . '% ' . round($chart_end, 2) . '%';
        $chart_start = $chart_end;
    }
    $donut_gradient = implode(', ', $chart_segments);
} else {
    $donut_gradient = '#d8e8f3 0% 100%';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css">
    <link rel="stylesheet" href="hms-theme.css">
    <style>
        :root {
            --bg-dark: #072a43;
            --bg-mid: #0e3b5f;
            --bg-soft: #f4f8fc;
            --text-dark: #12212e;
            --text-muted: #557085;
            --card: #ffffff;
            --primary: #0b7fab;
            --accent: #ef7f1a;
            --good: #198754;
            --warn: #f59f00;
            --shadow: 0 10px 28px rgba(7, 42, 67, 0.1);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            background:
                radial-gradient(circle at 15% 5%, #d8f3ff 0%, transparent 34%),
                radial-gradient(circle at 92% 92%, #dff0ff 0%, transparent 28%),
                #eef4f8;
            min-height: 100vh;
        }

        .mobile-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: linear-gradient(120deg, var(--bg-dark), var(--bg-mid));
            color: #fff;
            position: sticky;
            top: 0;
            z-index: 1100;
        }

        .menu-btn {
            border: 0;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--bg-dark), #062338);
            color: #dce9f4;
            padding: 24px 16px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.16);
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 10px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.14);
        }

        .sidebar-header h3 {
            margin: 0;
            color: #fff;
            letter-spacing: 0.3px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 18px 0 0;
            margin: 0;
        }

        .sidebar-menu li { margin-bottom: 6px; }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 12px;
            border-radius: 12px;
            color: #dce9f4;
            text-decoration: none;
            transition: 0.2s ease;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.13);
            color: #fff;
        }

        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            padding: 28px;
        }

        .hero {
            background: linear-gradient(125deg, #ffffff, #eef7ff);
            border-radius: 20px;
            padding: 22px;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 22px;
        }

        .hero h1 {
            margin: 0;
            font-size: 1.7rem;
        }

        .hero p {
            margin: 8px 0 0;
            color: var(--text-muted);
        }

        .quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            border: 1px solid #e2edf5;
            box-shadow: var(--shadow);
            padding: 18px;
            position: relative;
            overflow: hidden;
            text-decoration: none;
            color: var(--text-dark);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }

        .stat-card:before {
            content: "";
            position: absolute;
            top: -25px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: transparent;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--card-accent, #0b7fab);
            background: transparent;
            border: 2px solid var(--card-accent, #0b7fab);
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .stat-title {
            color: var(--text-muted);
            margin: 0 0 10px;
            font-size: 0.92rem;
            position: relative;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            position: relative;
        }

        .panel-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .panel {
            background: var(--card);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 18px;
        }

        .panel h2 {
            margin: 0 0 14px;
            font-size: 1.08rem;
        }

        .donut-wrap {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .donut-chart {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: conic-gradient(<?php echo $donut_gradient; ?>);
            position: relative;
            flex-shrink: 0;
        }

        .donut-chart::before {
            content: "";
            position: absolute;
            inset: 24px;
            border-radius: 50%;
            background: #fff;
        }

        .donut-center {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            text-align: center;
            z-index: 1;
        }

        .donut-center strong { font-size: 1.4rem; }

        .donut-center span {
            display: block;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .chart-legend {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 8px;
            min-width: 220px;
        }

        .chart-legend li {
            display: flex;
            justify-content: space-between;
            font-size: 0.92rem;
        }

        .legend-left {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .bars {
            display: grid;
            gap: 10px;
        }

        .bar-row {
            display: grid;
            gap: 6px;
        }

        .bar-top {
            display: flex;
            justify-content: space-between;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .bar-track {
            height: 10px;
            border-radius: 999px;
            background: #e3edf5;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: inherit;
            animation: growBar 0.8s ease;
        }

        @keyframes growBar {
            from { width: 0; }
        }

        .empty {
            padding: 16px;
            border: 1px dashed #c9d8e6;
            border-radius: 12px;
            color: var(--text-muted);
            text-align: center;
            background: #fbfdff;
        }

        @media (max-width: 1199px) {
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .panel-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 991px) {
            .mobile-topbar { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 16px;
            }
            .hero {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 560px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="stylesheet" href="css/buttons.css">

    
    
    
    <link rel='stylesheet' href='admin-sidebar.css'>
</head>
<body>
    <?php $admin_active = 'dashboard'; include 'admin_sidebar.php'; ?>

<div class="mobile-topbar">
        <strong>HMS Admin</strong>
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button>
    </div>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" width="40" height="40" alt="Logo">
                <h3>HMS</h3>
            </div>

            <ul class="sidebar-menu">
                <li><a href="AdminDashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="FeatchDoctors.php"><i class="fas fa-user-md"></i>Doctors</a></li>
                <li><a href="fetch_pharmacy.php"><i class="fas fa-pills"></i>Pharmacy</a></li>
                <li><a href="FeatchUserR.php"><i class="fas fa-hospital-user"></i>Users</a></li>
                <li><a href="admin_orders.php"><i class="fas fa-cart-shopping"></i>Orders</a></li>
                <li><a href="FeatchAppointments.php"><i class="fas fa-calendar-alt"></i>Appointments</a></li>
                <li><a href="FetchContect.php"><i class="fas fa-comments"></i>Feedback</a></li>
                <li><a href="Index2.html" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section class="hero">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p>Manage doctors, appointments, users, orders, pharmacy and feedback from one place.</p>
                </div>
                <div class="quick-actions">
                    <a href="FeatchAppointments.php" class="btn btn-primary hms-btn btn-primary-action"><i class="fas fa-calendar-check"></i>View Appointments</a>
                    <a href="admin_orders.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-bag-shopping"></i>Manage Orders</a>
                </div>
            </section>

            <section class="stats-grid">
                <?php foreach ($stats as $item): ?>
                <a href="<?php echo htmlspecialchars($item['link']); ?>" class="stat-card" style="--card-accent: <?php echo $item['color']; ?>;">
                    <span class="stat-icon"><i class="fas <?php echo $item['icon']; ?>"></i></span>
                    <p class="stat-title"><?php echo htmlspecialchars($item['label']); ?></p>
                    <p class="stat-value"><?php echo $item['count']; ?></p>
                </a>
                <?php endforeach; ?>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <h2>Category Distribution</h2>
                    <?php if ($total_records > 0): ?>
                    <div class="donut-wrap">
                        <div class="donut-chart">
                            <div class="donut-center">
                                <div>
                                    <strong><?php echo $total_records; ?></strong>
                                    <span>Total Records</span>
                                </div>
                            </div>
                        </div>
                        <ul class="chart-legend">
                            <?php foreach ($stats as $item): ?>
                            <li>
                                <span class="legend-left"><span class="dot" style="background: <?php echo $item['color']; ?>;"></span><?php echo htmlspecialchars($item['label']); ?></span>
                                <strong><?php echo $item['count']; ?></strong>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php else: ?>
                    <div class="empty">No records available to display chart data.</div>
                    <?php endif; ?>
                </article>

                <article class="panel">
                    <h2>Volume Comparison</h2>
                    <div class="bars">
                        <?php foreach ($stats as $item):
                            $bar_width = round(($item['count'] / $max_count) * 100, 2);
                        ?>
                        <div class="bar-row">
                            <div class="bar-top">
                                <span><?php echo htmlspecialchars($item['label']); ?></span>
                                <strong><?php echo $item['count']; ?></strong>
                            </div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width: <?php echo $bar_width; ?>%; background: linear-gradient(90deg, <?php echo $item['color']; ?>, #8ac9e1);"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <p style="margin: 0; color: var(--text-muted);">Highest volume: <strong style="color: var(--text-dark);"><?php echo htmlspecialchars($max_label); ?></strong> (<?php echo $max_value; ?>)</p>
                </article>
            </section>
        </main>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');

        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('show');
            });
        }
    </script>

    

    

    <script src='admin-sidebar.js'></script>
</body>
</html>







