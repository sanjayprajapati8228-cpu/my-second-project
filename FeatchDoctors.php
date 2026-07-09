<?php
@include('config.php');

$total_doctors = $conn->query("SELECT id FROM add_doctor")->num_rows;
$spec_row = $conn->query("SELECT COUNT(DISTINCT DoctorSpecialization) AS total FROM add_doctor")->fetch_assoc();
$total_specializations = isset($spec_row['total']) ? (int)$spec_row['total'] : 0;

// --- ACTION LOGIC (Delete & Email) ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    // Fetch user email and name before performing action
    $user_query = $conn->prepare("SELECT DoctorName, Email FROM add_doctor WHERE id = ?");
    $user_query->bind_param("i", $id);
    $user_query->execute();
    $user_data = $user_query->get_result()->fetch_assoc();

    if ($user_data) {
        $userName = $user_data['DoctorName'];
        $userEmail = $user_data['Email'];

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: HMS Admin <gujaratijeel15@gmail.com>\r\n";
        $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        if ($action == 'delete') {
            $delete = $conn->prepare("DELETE FROM add_doctor WHERE id = ?");
            $delete->bind_param("i", $id);

            if ($delete->execute()) {
                $subject = "Account Removed - HMS";
                $message = "
                <html><head><meta charset='UTF-8'><title>Doctor Account Removed</title>

    </head>
<body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
                <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                <tr><td style='background:linear-gradient(90deg,#8b1f1f,#bb3e3e);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Doctor Account Removed</td></tr>
                <tr><td style='padding:24px;line-height:1.7;'><p>Hello Dr. $userName,</p><p>Your HMS doctor account has been removed by the administrator.</p><p>If this is unexpected, contact HMS support.</p></td></tr>
                <tr><td style='padding:14px 24px;background:#fdf3f3;color:#8a4a4a;font-size:12px;'>HMS Management</td></tr>
                </table></td></tr></table>
</body></html>";

                @mail($userEmail, $subject, $message, $headers);
                echo "<script>alert('Doctor removed and Notification Sent.'); window.location.href='FeatchDoctors.php';</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Doctor Management - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
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

        .dropdown-icon {
            margin-left: auto;
            transition: transform 0.3s ease;
        }

        .has-submenu:hover > a .dropdown-icon {
            transform: rotate(180deg);
        }

        .submenu {
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

        .submenu li a {
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
            margin-bottom: 18px;
        }

        .hero h1 {
            margin: 0;
            font-size: 1.65rem;
            color: var(--primary);
            font-weight: 700;
        }

        .hero p {
            margin: 8px 0 0;
            color: var(--text-muted);
        }

        .summary-strip {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .summary-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e2edf5;
            box-shadow: var(--shadow);
            padding: 14px 16px;
        }

        .summary-card .label {
            color: var(--text-muted);
            font-size: 0.88rem;
        }

        .summary-card .value {
            margin-top: 6px;
            font-size: 1.55rem;
            font-weight: 700;
        }

        .table-panel {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2edf5;
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }

        .search-box {
            min-width: 260px;
            max-width: 360px;
            width: 100%;
        }

        .search-box .form-control {
            border-radius: 10px;
            border: 1px solid #d2e1ee;
            box-shadow: none;
        }

        .table thead {
            background-color: #12324a !important;
            color: #fff !important;
        }

        .table thead th {
            padding: 14px;
            border: none;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table td { vertical-align: middle; }

        .doctor-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #dce8f2;
            border-radius: 999px;
            padding: 5px 10px;
            background: #f7fbff;
            font-size: 0.88rem;
        }

        .empty {
            padding: 18px;
            border: 1px dashed #c9d8e6;
            border-radius: 12px;
            color: var(--text-muted);
            text-align: center;
            background: #fbfdff;
        }

        @media (max-width: 992px) {
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

        @media (max-width: 640px) {
            .summary-strip { grid-template-columns: 1fr; }
            .search-box {
                min-width: 100%;
                max-width: 100%;
            }
        }
    </style>

    
    
    <link rel='stylesheet' href='admin-sidebar.css'>
</head>
<body>
    <?php $admin_active = 'doctors'; include 'admin_sidebar.php'; ?>

<div class="mobile-topbar">
        <strong>HMS Admin</strong>
        <button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button>
    </div>

    <div class="layout">
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" width="35" alt="Logo">
                <h3>HMS</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="AdminDashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="has-submenu">
                    <a href="#" class="active"><i class="fas fa-user-md"></i> Manage Doctors <i class="fas fa-caret-down dropdown-icon"></i></a>
                    <ul class="submenu">
                        <li><a href="Add_Doctor.php">Add Doctors</a></li>
                        <li><a href="FeatchDoctors.php">View Doctors</a></li>
                    </ul>
                </li>
                <li class="has-submenu">
                    <a href="#"><i class="fas fa-pills"></i> Pharmacy <i class="fas fa-caret-down dropdown-icon"></i></a>
                    <ul class="submenu">
                        <li><a href="admin_add_medicine.php"> Add Pharmacy</a></li>
                        <li><a href="fetch_pharmacy.php" class="btn-secondary-action">View Pharmacy</a></li>
                    </ul>
                </li>
                <li><a href="FeatchUserR.php"><i class="fas fa-procedures"></i> Manage Users</a></li>
                <li><a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Manage Orders</a></li>
                <li><a href="FeatchAppointments.php"><i class="fas fa-calendar-alt"></i> Manage Appointments</a></li>
                <li><a href="FetchContect.php"><i class="fas fa-comments"></i> FeedBack</a></li>
                <li><a href="Index2.html" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <section class="hero">
                <div>
                    <h1><i class="fas fa-user-md"></i> Doctor Management</h1>
                    <p>Add, review, and remove doctor records from the admin panel.</p>
                </div>
                <div>
                    <a href="Add_Doctor.php" class="btn btn-primary hms-btn btn-primary-action"><i class="fas fa-plus-circle"></i> Add Doctor</a>
                </div>
            </section>

            <section class="summary-strip">
                <article class="summary-card">
                    <div class="label">Total Doctors</div>
                    <div class="value"><?php echo $total_doctors; ?></div>
                </article>
                <article class="summary-card">
                    <div class="label">Specializations</div>
                    <div class="value"><?php echo $total_specializations; ?></div>
                </article>
            </section>

            <div class="table-panel">
                <div class="toolbar">
                    <h5 class="fw-bold mb-0 text-dark">Current Doctors List</h5>
                    <div class="search-box">
                        <input type="text" id="doctorSearch" class="form-control" placeholder="Search by name, specialization, email, phone...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle hms-table" id="doctorTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Specialization</th>
                                <th>Doctor Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Password</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $results = $conn->query("SELECT * FROM add_doctor");
                            if ($results->num_rows > 0):
                                while ($data = $results->fetch_assoc()):
                            ?>
                            <tr>
                                <td><strong>#<?php echo (int)$data['id']; ?></strong></td>
                                <td><span class="doctor-chip"><i class="fas fa-stethoscope"></i><?php echo htmlspecialchars($data['DoctorSpecialization']); ?></span></td>
                                <td><?php echo htmlspecialchars($data['DoctorName']); ?></td>                                
                                <td><?php echo htmlspecialchars($data['DoctorContectNO']); ?></td>
                                <td><?php echo htmlspecialchars($data['Email']); ?></td>
                                <td><?php echo htmlspecialchars($data['Password']); ?></td>
                                <td class="text-center">
                                    <a href="?action=delete&id=<?php echo (int)$data['id']; ?>" class="btn-danger-action hms-icon-btn" title="Delete Doctor" onclick="return confirm('Are you sure you want to remove this doctor? A notification email will be sent.')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                            <tr>
                                <td colspan="8" class="py-4"><div class="empty">No doctors found in the records.</div></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const doctorSearch = document.getElementById('doctorSearch');
        const doctorTable = document.getElementById('doctorTable');

        if (menuBtn && sidebar) {
            menuBtn.addEventListener('click', function () {
                sidebar.classList.toggle('show');
            });
        }

        if (doctorSearch && doctorTable) {
            doctorSearch.addEventListener('input', function () {
                const q = doctorSearch.value.toLowerCase().trim();
                const rows = doctorTable.querySelectorAll('tbody tr');
                rows.forEach(function (row) {
                    if (row.querySelector('.empty')) return;
                    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
                });
            });
        }
    </script>

    

    

    <script src='admin-sidebar.js'></script>
</body>
</html>







