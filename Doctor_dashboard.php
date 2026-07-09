<?php
include 'config.php';
session_start();

if (!isset($_SESSION['doctor_id'])) {
    header('location:Doctor_Login.php');
    exit();
}

$doctor_id = (int)$_SESSION['doctor_id'];

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('location:Logins.html');
    exit();
}

$doctor = ['DoctorName' => 'Doctor'];
$doctor_stmt = $conn->prepare("SELECT DoctorName FROM add_doctor WHERE id = ? LIMIT 1");
if ($doctor_stmt) {
    $doctor_stmt->bind_param('i', $doctor_id);
    if ($doctor_stmt->execute()) {
        $res = $doctor_stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $doctor = $res->fetch_assoc();
        }
    }
    $doctor_stmt->close();
}

function doctor_count(mysqli $conn, string $query, int $doctor_id): int
{
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return 0;
    }
    $stmt->bind_param('i', $doctor_id);
    if (!$stmt->execute()) {
        $stmt->close();
        return 0;
    }
    $result = $stmt->get_result();
    $total = 0;
    if ($result && ($row = $result->fetch_assoc())) {
        $total = (int)$row['total'];
    }
    $stmt->close();
    return $total;
}

$total_appointments = doctor_count($conn, "SELECT COUNT(*) AS total FROM user_appointments WHERE doctor_id = ?", $doctor_id);
$pending_appointments = doctor_count($conn, "SELECT COUNT(*) AS total FROM user_appointments WHERE doctor_id = ? AND status = 'Pending'", $doctor_id);
$confirmed_appointments = doctor_count($conn, "SELECT COUNT(*) AS total FROM user_appointments WHERE doctor_id = ? AND status = 'Confirmed'", $doctor_id);
$patients_total = doctor_count($conn, "SELECT COUNT(DISTINCT patient_id) AS total FROM user_appointments WHERE doctor_id = ?", $doctor_id);
$schedule_slots = doctor_count($conn, "SELECT COUNT(*) AS total FROM doctor_schedule WHERE doctor_id = ?", $doctor_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css">
    <link rel="stylesheet" href="hms-theme.css">
    <style>
        :root {
            --bg-dark: #072a43;
            --bg-mid: #0e3b5f;
            --text-dark: #12212e;
            --text-muted: #557085;
            --card: #ffffff;
            --primary: #0b7fab;
            --shadow: 0 10px 28px rgba(7, 42, 67, 0.1);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            background: radial-gradient(circle at 15% 5%, #d8f3ff 0%, transparent 34%), radial-gradient(circle at 92% 92%, #dff0ff 0%, transparent 28%), #eef4f8;
            min-height: 100vh;
        }

        .mobile-topbar { display: none; align-items: center; justify-content: space-between; padding: 14px 16px; background: linear-gradient(120deg, var(--bg-dark), var(--bg-mid)); color: #fff; position: sticky; top: 0; z-index: 1100; }
        .menu-btn { border: 0; background: rgba(255, 255, 255, 0.2); color: #fff; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; }
        .layout { display: flex; min-height: 100vh; }

        .sidebar { width: 280px; background: linear-gradient(180deg, var(--bg-dark), #062338); color: #dce9f4; padding: 24px 16px; position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto; z-index: 1000; box-shadow: 4px 0 20px rgba(0, 0, 0, 0.16); transition: transform 0.3s ease; }
        .sidebar-header { display: flex; align-items: center; gap: 12px; padding: 0 10px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.14); }
        .sidebar-header h3 { margin: 0; color: #fff; }
        .sidebar-menu { list-style: none; padding: 18px 0 0; margin: 0; }
        .sidebar-menu li { margin-bottom: 6px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 10px; padding: 11px 12px; border-radius: 12px; color: #dce9f4; text-decoration: none; transition: 0.2s ease; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255, 255, 255, 0.13); color: #fff; }
        .has-submenu .dropdown-icon { margin-left: auto; transition: transform 0.3s ease; }
        .has-submenu:hover > a .dropdown-icon { transform: rotate(180deg); }
        .has-submenu .submenu { list-style: none; margin: 6px 0 0; padding: 0 0 0 12px; max-height: 0; overflow: hidden; transition: max-height 0.35s ease; }
        .has-submenu:hover .submenu { max-height: 220px; }
        .has-submenu .submenu a { font-size: 0.92rem; color: #bdd2e4; padding: 9px 12px; }

        .main-content { flex: 1; margin-left: 280px; padding: 24px; }
        .hero { background: linear-gradient(135deg, #ffffff, #f3f9ff); border-radius: 18px; padding: 20px; box-shadow: var(--shadow); border: 1px solid #dbe9f5; margin-bottom: 18px; }
        .hero h1 { margin: 0 0 8px; font-size: 1.6rem; }
        .hero p { margin: 0; color: var(--text-muted); }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 18px; }
        .stat-card { background: var(--card); border-radius: 14px; padding: 16px; box-shadow: var(--shadow); border: 1px solid #e2edf6; }
        .stat-title { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
        .stat-value { margin: 6px 0 0; font-size: 1.7rem; font-weight: 700; color: var(--bg-dark); }

        .panel-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .panel { background: #fff; border: 1px solid #e2edf6; border-radius: 14px; padding: 18px; box-shadow: var(--shadow); }
        .panel h2 { margin: 0 0 12px; font-size: 1.05rem; color: #14344c; }
        .quick-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }

        .btn-action { display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; border-radius: 10px; padding: 10px 12px; font-weight: 600; font-size: 0.9rem; }
        .btn-action.primary { background: var(--primary); color: #fff; }
        .btn-action.light { background: #eef5fb; color: #14405e; }

        @media (max-width: 991px) {
            .mobile-topbar { display: flex; }
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; padding: 16px; }
            .panel-grid { grid-template-columns: 1fr; }
            .quick-actions { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="stylesheet" href="css/buttons.css"></head>
<body>
    <div class="mobile-topbar">
        <strong>HMS Doctor Panel</strong>
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button>
    </div>

    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" width="40" height="40" alt="Logo">
                <h3>HMS</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="Doctor_dashboard.php" class="active"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="Doctor_profile.php"><i class="fa fa-user-md"></i>My Profile</a></li>
                <li class="has-submenu">
                    <a href="javascript:void(0)"><i class="fas fa-calendar-alt"></i>Appointments <i class="fas fa-caret-down dropdown-icon"></i></a>
                    <ul class="submenu">
                        <li><a href="DoctorPersonalAppointments.php"><i class="fas fa-list"></i>View Appointments</a></li>
                        <li><a href="DoctorsPersonalPatient.php"><i class="fas fa-user-injured"></i>My Patients</a></li>
                    </ul>
                </li>
                <li><a href="DoctorSchedule.php"><i class="fas fa-clock"></i>Doctor Schedule</a></li>
                <li><a href="Doctor_dashboard.php?logout=true" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section class="hero">
                <h1>Welcome, Dr. <?php echo htmlspecialchars($doctor['DoctorName']); ?></h1>
                <p>Manage your appointments, patients, profile, and availability from one doctor workspace.</p>
            </section>

            <section class="stats-grid">
                <article class="stat-card"><p class="stat-title">Total Appointments</p><p class="stat-value"><?php echo $total_appointments; ?></p></article>
                <article class="stat-card"><p class="stat-title">Pending Appointments</p><p class="stat-value"><?php echo $pending_appointments; ?></p></article>
                <article class="stat-card"><p class="stat-title">Confirmed Appointments</p><p class="stat-value"><?php echo $confirmed_appointments; ?></p></article>
                <article class="stat-card"><p class="stat-title">Total Patients</p><p class="stat-value"><?php echo $patients_total; ?></p></article>
                <article class="stat-card"><p class="stat-title">Schedule Slots</p><p class="stat-value"><?php echo $schedule_slots; ?></p></article>
            </section>

            <section class="panel-grid">
                <article class="panel">
                    <h2>Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="DoctorPersonalAppointments.php" class="btn btn-primary hms-btn btn-secondary-action"><i class="fas fa-calendar-check"></i>Appointments</a>
                        <a href="DoctorsPersonalPatient.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-user-injured"></i>Patients</a>
                        <a href="DoctorSchedule.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-clock"></i>Manage Schedule</a>
                        <a href="Doctor_profile.php" class="btn btn-outline-secondary hms-btn btn-secondary-action"><i class="fas fa-id-badge"></i>Edit Profile</a>
                    </div>
                </article>

                <article class="panel">
                    <h2>Account</h2>
                    <p style="margin:0;color:#557085;line-height:1.7;">Keep your schedule up to date so patients can book accurate slots and confirm pending requests promptly.</p>
                    <div style="margin-top:14px;">
                        <a href="Doctor_dashboard.php?logout=true" class="btn btn-danger hms-btn btn-danger-action" style="max-width:220px;"><i class="fas fa-power-off"></i>Sign Out</a>
                    </div>
                </article>
            </section>
        </main>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        if (menuBtn) {
            menuBtn.addEventListener('click', function () { sidebar.classList.toggle('show'); });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>






