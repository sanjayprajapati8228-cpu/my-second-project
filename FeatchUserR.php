<?php
@include 'config.php';

// --- DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    $stmt = $conn->prepare("SELECT Email, Fname FROM user_registration1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $userEmail = $user['Email'];
        $userName = $user['Fname'];

        $delete_stmt = $conn->prepare("DELETE FROM user_registration1 WHERE id = ?");
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
            $subject = "Account Deletion - HMS";
            $message = "
            <html><head><meta charset='UTF-8'><title>Account Deletion Notice</title></head>
            <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
            <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
            <tr><td style='background:linear-gradient(90deg,#8b1f1f,#bb3e3e);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Account Deletion Notice</td></tr>
            <tr><td style='padding:24px;line-height:1.7;'><p>Hello $userName,</p><p>Your HMS account has been deleted by administrator.</p><p>If this was not expected, contact support.</p></td></tr>
            <tr><td style='padding:14px 24px;background:#fdf3f3;color:#8a4a4a;font-size:12px;'>HMS Support Team</td></tr>
            </table></td></tr></table></body></html>";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: HMS Admin <gujaratijeel15@gmail.com>\r\n";
            $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
            @mail($userEmail, $subject, $message, $headers);

            echo "<script>alert('User deleted and email sent successfully'); window.location.href='FeatchUserR.php';</script>";
            exit();
        }
    } else {
        echo "<script>alert('User not found'); window.location.href='FeatchUserR.php';</script>";
        exit();
    }
}

$total_users = $conn->query("SELECT id FROM user_registration1")->num_rows;
$gender_row = $conn->query("SELECT COUNT(DISTINCT Gender) AS total FROM user_registration1")->fetch_assoc();
$total_genders = isset($gender_row['total']) ? (int)$gender_row['total'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users - HMS</title>
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

        .user-chip {
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
    <?php $admin_active = 'users'; include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <section class="hero">
            <div>
                <h1><i class="fas fa-users"></i> User Management</h1>
                <p>Review registered users and remove accounts when required.</p>
            </div>
        </section>

        <section class="summary-strip">
            <article class="summary-card">
                <div class="label">Total Users</div>
                <div class="value"><?php echo $total_users; ?></div>
            </article>
            <article class="summary-card">
                <div class="label">Gender Categories</div>
                <div class="value"><?php echo $total_genders; ?></div>
            </article>
        </section>

        <div class="table-panel">
            <div class="toolbar">
                <h5 class="fw-bold mb-0 text-dark">Registered Users</h5>
                <div class="search-box">
                    <input type="text" id="userSearch" class="form-control" placeholder="Search name, phone, email, address...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle hms-table" id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient Information</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $results = $conn->query("SELECT * FROM user_registration1 ORDER BY id DESC");
                        if($results->num_rows > 0):
                            while($data = $results->fetch_assoc()):
                        ?>
                        <tr>
                            <td><strong>#<?php echo (int)$data['id']; ?></strong></td>
                            <td><span class="user-chip"><i class="fas fa-user"></i><?php echo htmlspecialchars($data['Fname']); ?></span></td>
                            <td><?php echo htmlspecialchars($data['Gender']); ?></td>
                            <td><?php echo htmlspecialchars($data['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($data['Email']); ?></td>
                            <td><?php echo htmlspecialchars($data['Address']); ?></td>
                            <td class="text-center">
                                <button onclick="confirmDelete(<?php echo (int)$data['id']; ?>)" class="btn-danger-action hms-icon-btn">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="7" class="py-4"><div class="empty">No users found in the system.</div></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this user? An automated email will be sent.")) {
                window.location.href = "FeatchUserR.php?delete_id=" + id;
            }
        }

        const userSearch = document.getElementById('userSearch');
        const userTable = document.getElementById('userTable');

        if (userSearch && userTable) {
            userSearch.addEventListener('input', function () {
                const q = userSearch.value.toLowerCase().trim();
                const rows = userTable.querySelectorAll('tbody tr');
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
