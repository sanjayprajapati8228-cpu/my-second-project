<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:UserLogin.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:Logins.html');
    exit();
}

$user = [
    'Fname' => 'Patient',
    'Email' => '',
];

$select_user = mysqli_query($conn, "SELECT Fname, Email FROM `user_registration1` WHERE id = {$user_id} LIMIT 1");
if ($select_user && mysqli_num_rows($select_user) > 0) {
    $user = mysqli_fetch_assoc($select_user);
}

function get_count(mysqli $conn, string $query, int $user_id): int
{
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $result = $stmt->get_result();
    $value = 0;
    if ($result && ($row = $result->fetch_assoc())) {
        $value = (int)$row['total'];
    }
    $stmt->close();
    return $value;
}

$total_appointments = get_count(
    $conn,
    "SELECT COUNT(*) AS total FROM user_appointments WHERE patient_id = ?",
    $user_id
);
$pending_appointments = get_count(
    $conn,
    "SELECT COUNT(*) AS total FROM user_appointments WHERE patient_id = ? AND status = 'Pending'",
    $user_id
);
$confirmed_appointments = get_count(
    $conn,
    "SELECT COUNT(*) AS total FROM user_appointments WHERE patient_id = ? AND status = 'Confirmed'",
    $user_id
);
$orders_count = get_count(
    $conn,
    "SELECT COUNT(*) AS total FROM orders WHERE user_id = ?",
    $user_id
);

$next_appointment = null;
$next_stmt = $conn->prepare(
    "SELECT ua.appointment_Date, ua.appointment_Time, ua.status, ad.DoctorName
     FROM user_appointments ua
     LEFT JOIN add_doctor ad ON ua.doctor_id = ad.id
     WHERE ua.patient_id = ?
       AND (
           ua.appointment_Date > CURDATE()
           OR (ua.appointment_Date = CURDATE() AND ua.appointment_Time >= CURTIME())
       )
     ORDER BY ua.appointment_Date ASC, ua.appointment_Time ASC
     LIMIT 1"
);
if ($next_stmt) {
    $next_stmt->bind_param('i', $user_id);
    if ($next_stmt->execute()) {
        $next_result = $next_stmt->get_result();
        if ($next_result && $next_result->num_rows > 0) {
            $next_appointment = $next_result->fetch_assoc();
        }
    }
    $next_stmt->close();
}

$recent_rows = [];
$recent_stmt = $conn->prepare(
    "SELECT ua.id, ua.appointment_Date, ua.appointment_Time, ua.status, ua.cash, ad.DoctorName
     FROM user_appointments ua
     LEFT JOIN add_doctor ad ON ua.doctor_id = ad.id
     WHERE ua.patient_id = ?
     ORDER BY ua.id DESC
     LIMIT 5"
);
if ($recent_stmt) {
    $recent_stmt->bind_param('i', $user_id);
    if ($recent_stmt->execute()) {
        $recent_result = $recent_stmt->get_result();
        while ($recent_result && $row = $recent_result->fetch_assoc()) {
            $recent_rows[] = $row;
        }
    }
    $recent_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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

        * {
            box-sizing: border-box;
        }

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

        .sidebar-menu li {
            margin-bottom: 6px;
        }

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

        .has-submenu .dropdown-icon {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .has-submenu:hover > a .dropdown-icon {
            transform: rotate(180deg);
        }

        .has-submenu .submenu {
            list-style: none;
            margin: 6px 0 0;
            padding: 0 0 0 12px;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.35s ease;
        }

        .has-submenu:hover .submenu {
            max-height: 220px;
        }

        .has-submenu .submenu a {
            font-size: 0.92rem;
            color: #bdd2e4;
            padding: 9px 12px;
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
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.93rem;
            border: 1px solid transparent;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .btn-action.primary {
            background: linear-gradient(135deg, var(--primary), #0a6f95);
            color: #fff;
        }

        .btn-action.light {
            background: #fff;
            color: var(--bg-dark);
            border-color: #d6e6f3;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(11, 127, 171, 0.18);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 18px;
            position: relative;
            overflow: hidden;
        }

        .stat-card:before {
            content: "";
            position: absolute;
            top: -25px;
            right: -20px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(11, 127, 171, 0.09);
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
            grid-template-columns: 1.1fr 1fr;
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

        .next-app {
            border: 1px solid #dfebf6;
            border-left: 5px solid var(--accent);
            border-radius: 12px;
            padding: 14px;
            background: #fbfdff;
        }

        .next-app p {
            margin: 8px 0;
            color: var(--text-muted);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .status-pending {
            background: #fff4db;
            color: #9b6400;
        }

        .status-confirmed {
            background: #ddf5e7;
            color: #14663f;
        }

        .status-cancelled {
            background: #fde1e1;
            color: #972525;
        }

        .activity-table-wrap {
            overflow-x: auto;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 520px;
        }

        .activity-table th,
        .activity-table td {
            text-align: left;
            padding: 10px 8px;
            border-bottom: 1px solid #e8eff6;
            font-size: 0.92rem;
        }

        .activity-table th {
            color: var(--text-muted);
            font-weight: 600;
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
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .panel-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991px) {
            .mobile-topbar {
                display: flex;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

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
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="css/buttons.css"></head>
<body>
    <div class="mobile-topbar">
        <strong>HMS Dashboard</strong>
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button>
    </div>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" width="40" height="40" alt="Logo">
                <h3>HMS</h3>
            </div>

            <ul class="sidebar-menu">
                <li><a href="User_Dahboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="User_Profile.php"><i class="fas fa-user"></i>My Profile</a></li>
                <li class="has-submenu">
                    <a href="javascript:void(0)"><i class="fas fa-calendar-plus"></i>Appointments <i class="fas fa-caret-down dropdown-icon"></i></a>
                    <ul class="submenu">
                        <li><a href="User_appointment.php"><i class="fas fa-plus-circle"></i>New Appointment</a></li>
                        <li><a href="ViewAppointments.php"><i class="fas fa-list"></i>View Appointments</a></li>
                    </ul>
                </li>
                <li><a href="pharmacy.php"><i class="fas fa-pills"></i>Pharmacy</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-bag"></i>My Orders</a></li>
                <li><a href="?logout=1" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section class="hero">
                <div>
                    <h1>Welcome, <?php echo htmlspecialchars($user['Fname']); ?></h1>
                    <p>Your personal health dashboard with appointments, orders, and quick actions in one place.</p>
                </div>
                <div class="quick-actions">
                    <a href="User_appointment.php" class="btn btn-primary hms-btn btn-primary-action"><i class="fas fa-plus-circle"></i>Book Appointment</a>
                    <a href="ViewAppointments.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-calendar-check"></i>My Appointments</a>
                    <a href="User_Profile.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-id-badge"></i>Edit Profile</a>
                </div>
            </section>

            <section class="stats-grid">
                <article class="stat-card">
                    <p class="stat-title">Total Appointments</p>
                    <p class="stat-value"><?php echo $total_appointments; ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-title">Pending Approval</p>
                    <p class="stat-value"><?php echo $pending_appointments; ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-title">Confirmed Visits</p>
                    <p class="stat-value"><?php echo $confirmed_appointments; ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-title">Pharmacy Orders</p>
                    <p class="stat-value"><?php echo $orders_count; ?></p>
                </article>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <h2>Next Appointment</h2>
                    <?php if ($next_appointment) { ?>
                        <?php
                        $status = $next_appointment['status'] ?: 'Pending';
                        $status_class = 'status-pending';
                        if ($status === 'Confirmed') {
                            $status_class = 'status-confirmed';
                        } elseif ($status === 'Cancelled') {
                            $status_class = 'status-cancelled';
                        }
                        ?>
                        <div class="next-app">
                            <p><strong>Doctor:</strong> Dr. <?php echo htmlspecialchars($next_appointment['DoctorName'] ?: 'Not Assigned'); ?></p>
                            <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($next_appointment['appointment_Date'])); ?></p>
                            <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($next_appointment['appointment_Time'])); ?></p>
                            <p><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></p>
                        </div>
                    <?php } else { ?>
                        <div class="empty">
                            No upcoming appointments. Book one now to avoid delays.
                        </div>
                    <?php } ?>
                </article>

                <article class="panel">
                    <h2>Quick Links</h2>
                    <div class="quick-actions">
                        <a href="patient_appointmenthistory.php" class="btn btn-outline-secondary hms-btn btn-navigation"><i class="fas fa-history"></i>Appointment History</a>
                        <a href="orders.php" class="btn btn-outline-secondary hms-btn btn-navigation"><i class="fas fa-truck"></i>Track Orders</a>
                        <a href="pharmacy.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-capsules"></i>Buy Medicines</a>
                        <a href="?logout=1" class="btn btn-danger hms-btn btn-danger-action"><i class="fas fa-power-off"></i>Sign Out</a>
                    </div>
                </article>
            </section>

            <section class="panel" style="margin-top: 18px;">
                <h2>Recent Appointment Activity</h2>
                <?php if (!empty($recent_rows)) { ?>
                    <div class="activity-table-wrap">
                        <table class="table table-striped table-hover align-middle hms-table activity-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Fees</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_rows as $item) { ?>
                                    <?php
                                    $status = $item['status'] ?: 'Pending';
                                    $status_class = 'status-pending';
                                    if ($status === 'Confirmed') {
                                        $status_class = 'status-confirmed';
                                    } elseif ($status === 'Cancelled') {
                                        $status_class = 'status-cancelled';
                                    }
                                    ?>
                                    <tr>
                                        <td>#<?php echo (int)$item['id']; ?></td>
                                        <td>Dr. <?php echo htmlspecialchars($item['DoctorName'] ?: 'Not Assigned'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($item['appointment_Date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($item['appointment_Time'])); ?></td>
                                        <td>Rs. <?php echo (int)$item['cash']; ?></td>
                                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="empty">No appointment records yet.</div>
                <?php } ?>
            </section>
        </main>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');

        if (menuBtn) {
            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('show');
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>







