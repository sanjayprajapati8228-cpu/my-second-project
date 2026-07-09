<?php
include 'config.php';
session_start();

// --- Logic for Confirming Order ---
if(isset($_GET['confirm'])){
    $order_id = (int)$_GET['confirm'];

    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id'");
    $order_data = mysqli_fetch_assoc($order_query);
    $user_email = $order_data['email'];

    mysqli_query($conn, "UPDATE `orders` SET status = 'Confirmed' WHERE id = '$order_id'");

    $to = $user_email;
    $subject = "Order Confirmed - HMS Pharmacy";
    $message = "
    <html><head><meta charset='UTF-8'><title>Order Confirmed</title></head>
    <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
    <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
    <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Order Confirmed</td></tr>
    <tr><td style='padding:24px;line-height:1.7;'><p>Dear Customer,</p><p>Your order <strong>#$order_id</strong> is confirmed and being prepared for delivery.</p><p>Thank you for choosing HMS Pharmacy.</p></td></tr>
    <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Pharmacy Team</td></tr>
    </table></td></tr></table></body></html>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: HMS Pharmacy <gujaratijeel15@gmail.com>\r\n";
    $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
    @mail($to, $subject, $message, $headers);

    header('location:admin_orders.php');
    exit();
}

// --- Logic for Deleting Order ---
if(isset($_GET['delete'])){
    $order_id = (int)$_GET['delete'];

    $order_query = mysqli_query($conn, "SELECT * FROM `orders` WHERE id = '$order_id'");
    $order_data = mysqli_fetch_assoc($order_query);
    $user_email = $order_data['email'];

    mysqli_query($conn, "DELETE FROM `orders` WHERE id = '$order_id'");

    $to = $user_email;
    $subject = "Order Cancelled - HMS Pharmacy";
    $message = "
    <html><head><meta charset='UTF-8'><title>Order Cancelled</title></head>
    <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
    <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
    <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
    <tr><td style='background:linear-gradient(90deg,#8b1f1f,#bb3e3e);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Order Cancelled</td></tr>
    <tr><td style='padding:24px;line-height:1.7;'><p>Dear Customer,</p><p>Your order <strong>#$order_id</strong> has been cancelled.</p><p>If payment was made online, refund will be initiated shortly.</p></td></tr>
    <tr><td style='padding:14px 24px;background:#fdf3f3;color:#8a4a4a;font-size:12px;'>HMS Pharmacy Team</td></tr>
    </table></td></tr></table></body></html>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: HMS Pharmacy <gujaratijeel15@gmail.com>\r\n";
    $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
    @mail($to, $subject, $message, $headers);

    header('location:admin_orders.php');
    exit();
}

$total_orders = $conn->query("SELECT id FROM orders")->num_rows;
$pending_orders = $conn->query("SELECT id FROM orders WHERE status = 'Pending'")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - HMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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
    <?php $admin_active = 'orders'; include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <section class="hero">
            <div>
                <h1><i class="fas fa-shopping-cart"></i> Order Management</h1>
                <p>Track pharmacy orders, confirm delivery requests, and handle cancellation workflow.</p>
            </div>
        </section>

        <section class="summary-strip">
            <article class="summary-card">
                <div class="label">Total Orders</div>
                <div class="value"><?php echo $total_orders; ?></div>
            </article>
            <article class="summary-card">
                <div class="label">Pending Orders</div>
                <div class="value"><?php echo $pending_orders; ?></div>
            </article>
        </section>

        <div class="table-panel">
            <div class="toolbar">
                <h5 class="fw-bold mb-0 text-dark">All Pharmacy Orders</h5>
                <div class="search-box">
                    <input type="text" id="orderSearch" class="form-control" placeholder="Search customer, phone, payment, status...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle hms-table" id="orderTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Total Price</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $all_orders = mysqli_query($conn, "SELECT * FROM `orders` ORDER BY id DESC");
                        if(mysqli_num_rows($all_orders) > 0){
                            while($row = mysqli_fetch_assoc($all_orders)){
                                $status_class = ($row['status'] == 'Confirmed') ? 'bg-success' : 'bg-warning text-dark';
                        ?>
                        <tr>
                            <td>#<?php echo (int)$row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><small><?php echo htmlspecialchars($row['address']); ?></small></td>
                            <td><strong>&#8377;<?php echo number_format((float)$row['total_price'], 2); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                            <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                            <td class="text-center">
                                <?php if($row['status'] == 'Pending'): ?>
                                <a href="admin_orders.php?confirm=<?php echo (int)$row['id']; ?>" class="btn-success-action mb-1" onclick="return confirm('Confirm this order and notify user?')">
                                    <i class="fas fa-check"></i> Confirm
                                </a>
                                <?php endif; ?>

                                <a href="admin_orders.php?delete=<?php echo (int)$row['id']; ?>" class="btn-danger-action mb-1" onclick="return confirm('Delete this order and notify user?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='9' class='py-4'><div class='empty'>No orders found in database.</div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const orderSearch = document.getElementById('orderSearch');
        const orderTable = document.getElementById('orderTable');

        if (orderSearch && orderTable) {
            orderSearch.addEventListener('input', function () {
                const q = orderSearch.value.toLowerCase().trim();
                const rows = orderTable.querySelectorAll('tbody tr');
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
