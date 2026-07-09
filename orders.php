<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:UserLogin.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <style>
        body {
            margin: 0;
            padding: 16px;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: radial-gradient(circle at 12% 0%, #d9edff 0%, transparent 34%), #edf4fa;
            color: #173247;
        }
        .page-wrap { max-width: 980px; margin: 0 auto; }
        .topbar {
            background: #fff;
            border: 1px solid #dbe7f1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .topbar h4 { margin: 0; color: #0d3d5a; font-size: 1.03rem; }
        .topbar a { text-decoration: none; color: #2459d2; font-weight: 600; }
        .box {
            background: #fff;
            border: 1px solid #dbe7f1;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 12px 30px rgba(8,46,72,0.08);
        }
        .table thead th {
            background: #0d3d5a !important;
            color: #fff !important;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            font-size: 12px;
        }
    </style>
    <link rel="stylesheet" href="css/buttons.css"></head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <h4>HMS Pharmacy Orders</h4>
            <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
        </div>
        <div class="box">
        <h3>My Order History</h3>
        <div class="table-responsive">
        <table class="table table-striped table-hover mt-3 align-middle hms-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $orders = mysqli_query($conn, "SELECT * FROM `orders` WHERE user_id = '$user_id' ");
                if(mysqli_num_rows($orders) > 0){
                while($row = mysqli_fetch_assoc($orders)){
                    echo "<tr>
                        <td>#{$row['id']}</td>
                        <td>{$row['order_date']}</td>
                        <td>{$row['payment_method']}</td>
                        <td>{$row['status']}</td>
                    </tr>";
                }
                } else {
                    echo "<tr><td colspan='4' class='text-center py-4 text-muted'>No orders found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </div>
        </div>
    </div>
</body>
</html>





