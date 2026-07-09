<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:UserLogin.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];

// Handle Quantity Updates within the Checkout Page
if (isset($_POST['update_cart'])) {
    $error_msg = "";
    foreach ($_POST['qty'] as $id => $new_qty) {
        $id = (int)$id;
        $new_qty = (int)$new_qty;

        if ($new_qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            // --- ADDED STOCK VALIDATION CHECK ---
            $stock_check = mysqli_query($conn, "SELECT name, stock FROM `products` WHERE id = '$id'");
            $product_data = mysqli_fetch_assoc($stock_check);
            $available_stock = (int)$product_data['stock'];

            if ($new_qty > $available_stock) {
                $_SESSION['cart'][$id] = $available_stock; // Set to max available
                $error_msg .= "Only $available_stock units of " . $product_data['name'] . " are available. ";
            } else {
                $_SESSION['cart'][$id] = $new_qty;
            }
        }
    }
    
    if ($error_msg != "") {
        echo "<script>alert('$error_msg'); window.location.href='checkout.php';</script>";
        exit();
    }
    header('location:checkout.php');
    exit();
}

if (isset($_POST['place_order'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $method = $_POST['payment_method'];

    $grand_total = 0;
    $item_details = "";
    
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $id => $qty) {
            $product_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$id'");
            $fetch_product = mysqli_fetch_assoc($product_query);
            $sub_total = $fetch_product['price'] * $qty;
            $grand_total += $sub_total;
            $item_details .= $fetch_product['name'] . " (Qty: $qty) - Rs. " . number_format($sub_total, 2) . "\n";
            
            // Deduct stock from database after order is placed
            $new_stock = $fetch_product['stock'] - $qty;
            mysqli_query($conn, "UPDATE `products` SET stock = '$new_stock' WHERE id = '$id'");
        }
    }

    $insert = mysqli_query($conn, "INSERT INTO `orders` (user_id, full_name, address, phone, payment_method, total_price, email) 
                               VALUES ('$user_id', '$name', '$address', '$phone', '$method', '$grand_total', '$email')");

    if ($insert) {
        $to = $email;
        $subject = "Order Receipt - HMS Pharmacy";
        $message = "
        <html>
        <head><meta charset='UTF-8'><title>Order Receipt</title></head>
        <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'>
                <tr><td align='center'>
                    <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                        <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Pharmacy Order Receipt</td></tr>
                        <tr><td style='padding:24px;line-height:1.7;'>
                            <p style='margin:0 0 12px;'>Hello $name, your order has been placed successfully.</p>
                            <p style='margin:0 0 8px;'><strong>Order Items:</strong></p>
                            <pre style='margin:0;background:#f8fbfe;border:1px solid #e2edf5;border-radius:10px;padding:12px;white-space:pre-wrap;font-family:Arial,sans-serif;'>$item_details</pre>
                            <p style='margin:14px 0 0;'><strong>Total:</strong> Rs. " . number_format($grand_total, 2) . "</p>
                        </td></tr>
                        <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Pharmacy Team</td></tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: HMS Pharmacy <gujaratijeel15@gmail.com>\r\n";
        $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
        mail($to, $subject, $message, $headers);

        $_SESSION['cart'] = [];
        echo "<script>alert('Order Placed! Receipt sent to email.'); window.location.href='orders.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout & Update Order</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <style>
        body {
            background: radial-gradient(circle at 12% 0%, #d9edff 0%, transparent 34%), #edf4fa !important;
            font-family: "Segoe UI", Tahoma, sans-serif;
            color: #173247;
        }
        .page-wrap { max-width: 1100px; margin: 0 auto; }
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
        .topbar h5 { margin: 0; color: #0d3d5a; }
        .topbar a { text-decoration: none; color: #2459d2; font-weight: 600; }
        .card {
            border: 1px solid #dbe7f1 !important;
            border-radius: 12px !important;
            box-shadow: 0 12px 30px rgba(8,46,72,0.08) !important;
        }
        #qr_code_section { display: none; text-align: center; margin-top: 15px; padding: 15px; border: 2px dashed #8faed0; border-radius: 10px; background: #f8fbff; }
        .item-row { border-bottom: 1px solid #e2ecf5; padding: 10px 0; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body class="bg-light">
    <div class="container mt-4 mb-5 page-wrap">
        <div class="topbar">
            <h5>HMS Pharmacy Checkout</h5>
            <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="card p-4 shadow-sm mb-4">
                    <h4>Review & Update Order</h4>
                    <hr>
                    <form method="post">
                        <?php 
                        $total = 0;
                        if(!empty($_SESSION['cart'])):
                            foreach($_SESSION['cart'] as $id => $qty):
                                $p = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
                                $r = mysqli_fetch_assoc($p);
                                $sub = $r['price'] * $qty;
                                $total += $sub;
                        ?>
                        <div class="row item-row align-items-center">
                            <div class="col-6">
                                <strong><?php echo $r['name']; ?></strong><br>
                                <small class="text-muted">&#8377;<?php echo $r['price']; ?> each</small>
                                <br><small class="text-danger">Stock: <?php echo $r['stock']; ?></small>
                            </div>
                            <div class="col-4">
                                <input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $qty; ?>" min="0" max="<?php echo $r['stock']; ?>" class="form-control form-control-sm">
                            </div>
                            <div class="col-2 text-end">
                                <span>&#8377;<?php echo number_format($sub, 0); ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <h5>Grand Total:</h5>
                            <h5 class="text-success">&#8377;<?php echo number_format($total, 2); ?></h5>
                        </div>
                        
                        <button type="submit" name="update_cart" class="btn btn-warning btn-sm w-100 mt-2 btn-secondary-action">Update Quantities</button>
                        <a href="pharmacy.php" class="btn btn-outline-primary btn-sm w-100 mt-2 btn-primary-action">Add More Medicines</a>
                        
                        <?php else: ?>
                            <p class="text-center">Your cart is empty.</p>
                            <a href="pharmacy.php" class="btn btn-primary w-100 btn-secondary-action">Go to Pharmacy</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card p-4 shadow-sm border-0">
                    <h3 class="mb-4">Delivery Details</h3>
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Delivery Address</label>
                            <textarea name="address" class="form-control" rows="2" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-primary fw-bold">Select Payment Method</label>
                            <select name="payment_method" id="payment_method" class="form-select" onchange="toggleQR()" required>
                                <option value="COD">Cash on Delivery (COD)</option>
                                <option value="Online">Online Payment (UPI / QR)</option>
                            </select>
                        </div>

                        <div id="qr_code_section">
                            <p class="mb-2 text-primary fw-bold">Scan to Pay via Any UPI App</p>
                            <img src="images/qr-code.png" alt="QR Code" style="width: 180px;">
                            <p class="small text-muted mt-2">Please complete payment before clicking Confirm.</p>
                        </div>

                        <button type="submit" name="place_order" class="btn btn-success w-100 py-3 mt-3 fw-bold shadow btn-primary-action">
                            CONFIRM & PAY &#8377;<?php echo number_format($total, 2); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleQR() {
        var method = document.getElementById("payment_method").value;
        document.getElementById("qr_code_section").style.display = (method === "Online") ? "block" : "none";
    }
    </script>
</body>
</html>