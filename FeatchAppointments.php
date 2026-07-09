<?php
@include('config.php');

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    $user_query = $conn->prepare("SELECT Name, Email FROM user_appointments WHERE id = ?");
    $user_query->bind_param("i", $id);
    $user_query->execute();
    $user_data = $user_query->get_result()->fetch_assoc();

    if ($user_data) {
        $userName = $user_data['Name'];
        $userEmail = $user_data['Email'];
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: HMS Admin <gujaratijeel15@gmail.com>\r\n";
        $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";

        if ($action == 'delete') {
            $delete = $conn->prepare("DELETE FROM user_appointments WHERE id = ?");
            $delete->bind_param("i", $id);
            if ($delete->execute()) {
                $message = "
                <html><head><meta charset='UTF-8'><title>Appointment Cancelled</title></head>
                <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
                <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                <tr><td style='background:linear-gradient(90deg,#8b1f1f,#bb3e3e);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Appointment Cancelled</td></tr>
                <tr><td style='padding:24px;line-height:1.7;'><p>Hello $userName,</p><p>Your appointment has been cancelled by administrator.</p></td></tr>
                <tr><td style='padding:14px 24px;background:#fdf3f3;color:#8a4a4a;font-size:12px;'>HMS Team</td></tr>
                </table></td></tr></table></body></html>";
                @mail($userEmail, "Appointment Cancelled", $message, $headers);
                echo "<script>alert('Deleted'); window.location.href='FeatchAppointments.php';</script>";
                exit();
            }
        }
    }
}

$total_appointments = $conn->query("SELECT id FROM user_appointments")->num_rows;
$pending_appointments = $conn->query("SELECT id FROM user_appointments WHERE status = 'Pending'")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Appointments Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
    <link rel='stylesheet' href='admin-sidebar.css'>
    <style>
        :root {
            --text-dark: #12212e;
            --text-muted: #557085;
            --shadow: 0 10px 28px rgba(7, 42, 67, 0.1);
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

        .main-content { padding: 28px; }

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
            color: #0b7fab;
            font-weight: 700;
        }

        .hero p { margin: 8px 0 0; color: var(--text-muted); }

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

        .summary-card .label { color: var(--text-muted); font-size: 0.88rem; }
        .summary-card .value { margin-top: 6px; font-size: 1.55rem; font-weight: 700; }

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

        .search-box { min-width: 260px; max-width: 360px; width: 100%; }
        .search-box .form-control { border-radius: 10px; border: 1px solid #d2e1ee; box-shadow: none; }

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

        .status-badge {
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending { background: #fff4db; color: #9b6400; }
        .status-confirmed { background: #ddf5e7; color: #14663f; }
        .status-cancelled { background: #fde1e1; color: #972525; }

        .empty {
            padding: 18px;
            border: 1px dashed #c9d8e6;
            border-radius: 12px;
            color: var(--text-muted);
            text-align: center;
            background: #fbfdff;
        }

        @media (max-width: 992px) {
            .main-content { padding: 16px; }
            .hero { flex-direction: column; align-items: flex-start; }
        }

        @media (max-width: 640px) {
            .summary-strip { grid-template-columns: 1fr; }
            .search-box { min-width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>
    <?php $admin_active = 'appointments'; include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <section class="hero">
            <div>
                <h1><i class="fas fa-calendar-alt"></i> Appointment Management</h1>
                <p>Track all booking requests and remove invalid appointment records.</p>
            </div>
        </section>

        <section class="summary-strip">
            <article class="summary-card">
                <div class="label">Total Appointments</div>
                <div class="value"><?php echo $total_appointments; ?></div>
            </article>
            <article class="summary-card">
                <div class="label">Pending Appointments</div>
                <div class="value"><?php echo $pending_appointments; ?></div>
            </article>
        </section>

        <div class="table-panel">
            <div class="toolbar">
                <h5 class="fw-bold mb-0 text-dark">Appointment Records</h5>
                <div class="search-box">
                    <input type="text" id="appointmentSearch" class="form-control" placeholder="Search patient, doctor, date, status...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle hms-table" id="appointmentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $results = $conn->query("SELECT * FROM user_appointments ORDER BY id DESC");
                        if ($results && $results->num_rows > 0):
                            while($data = $results->fetch_assoc()):
                                $status = $data['status'] ?: 'Pending';
                                $status_class = 'status-pending';
                                if ($status === 'Confirmed') { $status_class = 'status-confirmed'; }
                                if ($status === 'Cancelled') { $status_class = 'status-cancelled'; }
                        ?>
                        <tr>
                            <td>#<?php echo (int)$data['id']; ?></td>
                            <td><?php echo htmlspecialchars($data['Name']); ?></td>
                            <td><?php echo htmlspecialchars($data['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($data['appointment_Date']); ?></td>
                            <td><?php echo htmlspecialchars($data['appointment_Time']); ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                            echo '<tr><td colspan="6" class="py-4"><div class="empty">No appointments found.</div></td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const appointmentSearch = document.getElementById('appointmentSearch');
        const appointmentTable = document.getElementById('appointmentTable');

        if (appointmentSearch && appointmentTable) {
            appointmentSearch.addEventListener('input', function () {
                const q = appointmentSearch.value.toLowerCase().trim();
                const rows = appointmentTable.querySelectorAll('tbody tr');
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
