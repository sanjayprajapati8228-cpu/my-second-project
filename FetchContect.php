<?php
@include('config.php');

if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $conn->query("DELETE FROM contect_form WHERE id = '$delete_id'");
    header('location:FetchContect.php');
    exit();
}

$total_feedback = $conn->query("SELECT id FROM contect_form")->num_rows;
$email_row = $conn->query("SELECT COUNT(DISTINCT Email) AS total FROM contect_form")->fetch_assoc();
$unique_senders = isset($email_row['total']) ? (int)$email_row['total'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Feedback Management</title>
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
    <?php $admin_active = 'feedback'; include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <section class="hero">
            <div>
                <h1><i class="fas fa-comment-dots"></i> Feedback Management</h1>
                <p>Monitor user messages and remove records that are no longer required.</p>
            </div>
        </section>

        <section class="summary-strip">
            <article class="summary-card">
                <div class="label">Total Feedback</div>
                <div class="value"><?php echo $total_feedback; ?></div>
            </article>
            <article class="summary-card">
                <div class="label">Unique Senders</div>
                <div class="value"><?php echo $unique_senders; ?></div>
            </article>
        </section>

        <div class="table-panel">
            <div class="toolbar">
                <h5 class="fw-bold mb-0 text-dark">User Feedback Records</h5>
                <div class="search-box">
                    <input type="text" id="feedbackSearch" class="form-control" placeholder="Search name, email, subject, message...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle hms-table" id="feedbackTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Mobile</th>
                            <th>Message</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $results = $conn->query("SELECT * FROM contect_form ORDER BY id DESC");
                        if ($results && $results->num_rows > 0):
                            while($data = $results->fetch_assoc()):
                        ?>
                        <tr>
                            <td>#<?php echo (int)$data['id']; ?></td>
                            <td><?php echo htmlspecialchars($data['Name']); ?></td>
                            <td><?php echo htmlspecialchars($data['Email']); ?></td>
                            <td><?php echo htmlspecialchars($data['Subject']); ?></td>
                            <td><?php echo htmlspecialchars($data['MobileNumber']); ?></td>
                            <td><small><?php echo htmlspecialchars($data['Message']); ?></small></td>
                            <td class="text-center">
                                <form method="POST" onsubmit="return confirm('Delete this feedback?');" class="d-inline">
                                    <input type="hidden" name="delete_id" value="<?php echo (int)$data['id']; ?>">
                                    <button type="submit" class="btn-danger-action hms-icon-btn"><i class="fas fa-trash-alt"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php
                            endwhile;
                        else:
                            echo '<tr><td colspan="7" class="py-4"><div class="empty">No feedback records found.</div></td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const feedbackSearch = document.getElementById('feedbackSearch');
        const feedbackTable = document.getElementById('feedbackTable');

        if (feedbackSearch && feedbackTable) {
            feedbackSearch.addEventListener('input', function () {
                const q = feedbackSearch.value.toLowerCase().trim();
                const rows = feedbackTable.querySelectorAll('tbody tr');
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
