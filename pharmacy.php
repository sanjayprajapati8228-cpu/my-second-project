<?php
include 'config.php';
session_start();

if(!isset($_SESSION['user_id'])){
    header('location:UserLogin.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$added_message = '';

if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if(isset($_POST['add_to_cart'])){
    $product_id = (int)$_POST['product_id'];
    $new_qty = (int)$_POST['quantity'];

    if ($new_qty < 1) {
        $new_qty = 1;
    }

    // Check availability from database
    $check_stock_query = mysqli_query($conn, "SELECT name, stock FROM products WHERE id = $product_id");
    $stock_data = mysqli_fetch_assoc($check_stock_query);
    $available_stock = (int)$stock_data['stock'];
    $product_name = $stock_data['name'];

    // Calculate how many are ALREADY in the cart for this specific product
    $current_in_cart = isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
    $total_requested = $current_in_cart + $new_qty;

    // VALIDATION: Total in cart + New request must not exceed stock
    if($total_requested <= $available_stock) {
        if(isset($_SESSION['cart'][$product_id])){
            $_SESSION['cart'][$product_id] += $new_qty;
        } else {
            $_SESSION['cart'][$product_id] = $new_qty;
        }
        $added_message = "$product_name added to cart. Total in cart: " . $_SESSION['cart'][$product_id];
    } else {
        // Provide clear feedback on how many more they can actually add
        $remaining_allowed = $available_stock - $current_in_cart;
        if($remaining_allowed <= 0) {
            $added_message = "Error: You already have the maximum available stock ($available_stock) of $product_name in your cart.";
        } else {
            $added_message = "Error: Cannot add $new_qty more. You only have $remaining_allowed left in stock for $product_name.";
        }
    }
}

// Calculate total cart items for the icon badge
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacy Store</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: radial-gradient(circle at 12% 0%, #d9edff 0%, transparent 34%), #edf4fa;
            color: #173247;
        }

        .page-wrap {
            max-width: 1180px;
            margin: 0 auto;
            padding: 16px;
        }

        .topbar {
            background: #fff;
            border: 1px solid #dbe7f1;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 25px rgba(8, 46, 72, 0.06);
        }

        .topbar h4 {
            margin: 0;
            font-size: 1.06rem;
            color: #0d3d5a;
        }

        .bar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-navigation {
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #d8e5f2;
            border-radius: 8px;
            background: #f8fbff;
            color: #26506a;
            font-weight: 600;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .btn-navigation:hover {
            background: #eef4ff;
        }

        .cart-icon {
            position: relative;
            text-decoration: none;
            color: #fff;
            background: linear-gradient(135deg, #2463eb, #1a4ec8);
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 0.95rem;
            font-weight: 600;
            box-shadow: 0 8px 20px rgba(36, 99, 235, 0.24);
        }

        .cart-icon .badge {
            font-size: 0.7rem;
            position: absolute;
            top: -6px;
            right: -7px;
        }

        .content-box {
            background: #fff;
            border: 1px solid #dbe7f1;
            border-radius: 14px;
            padding: 18px;
            box-shadow: 0 12px 30px rgba(8, 46, 72, 0.08);
        }

        .content-box h2 {
            text-align: center;
            color: #0d3d5a;
            margin-bottom: 20px;
            font-size: 1.6rem;
            font-weight: 700;
        }

        .medicine-card {
            border: 1px solid #d9e7f2;
            border-radius: 12px;
            transition: 0.25s ease;
            background: #fbfdff;
        }

        .medicine-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(7, 41, 64, 0.12) !important;
        }

        .medicine-thumb-wrap {
            width: 100%;
            height: 170px;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .medicine-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .price-tag {
            color: #1f8a5b;
            font-weight: 700;
            font-size: 1.15rem;
        }

        .stock-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 6px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .medicine-modal-thumb {
            width: 100%;
            max-width: 230px;
            height: 170px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="topbar">
            <h4>HMS Pharmacy</h4>
            <div class="bar-actions">
                <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
                <a href="checkout.php" class="cart-icon">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <span class="badge bg-danger rounded-pill"><?php echo $cart_count; ?></span>
                </a>
            </div>
        </div>

        <?php if($added_message !== ''): ?>
            <div class="alert <?php echo (strpos($added_message, 'Error') !== false) ? 'alert-danger' : 'alert-info'; ?> border-0 shadow-sm" role="alert">
                <?php echo $added_message; ?>
            </div>
        <?php endif; ?>

        <div class="content-box">
            <h2>Hospital Pharmacy</h2>
            <div class="row">
                <?php
                $result = mysqli_query($conn, "SELECT * FROM products");
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){
                        $is_out_of_stock = ($row['stock'] <= 0);
                        // Show current cart status for each item
                        $already_in_cart = isset($_SESSION['cart'][$row['id']]) ? $_SESSION['cart'][$row['id']] : 0;
                ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card medicine-card h-100 shadow-sm p-3">
                        <div class="medicine-thumb-wrap">
                            <img src="images/<?php echo $row['image']; ?>" class="card-img-top mx-auto medicine-thumb" alt="medicine">
                        </div>
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo $row['name']; ?></h5>
                            
                            <?php if($is_out_of_stock): ?>
                                <span class="badge bg-danger stock-badge">Out of Stock</span>
                            <?php else: ?>
                                <span class="badge bg-success stock-badge">Stock: <?php echo $row['stock']; ?></span>
                                <?php if($already_in_cart > 0): ?>
                                    <br><small class="text-primary fw-bold">In Cart: <?php echo $already_in_cart; ?></small>
                                <?php endif; ?>
                            <?php endif; ?>

                            <p class="price-tag">Rs.<?php echo number_format($row['price'], 2); ?></p>

                            <button type="button" class="btn btn-outline-info btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#descModal<?php echo $row['id']; ?>">
                                <i class="fas fa-info-circle"></i> Description
                            </button>

                            <form method="post">
                                <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                <div class="input-group mb-2 qty-group">
                                    <span class="input-group-text">Qty</span>
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo ($row['stock'] - $already_in_cart); ?>" class="form-control" <?php echo ($is_out_of_stock || ($row['stock'] - $already_in_cart) <= 0) ? 'disabled' : 'required'; ?>>
                                </div>
                                <?php if($is_out_of_stock): ?>
                                    <button type="button" class="btn btn-secondary w-100" disabled>Unavailable</button>
                                <?php elseif(($row['stock'] - $already_in_cart) <= 0): ?>
                                    <button type="button" class="btn btn-warning w-100" disabled>Max Added</button>
                                <?php else: ?>
                                    <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="descModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"><?php echo $row['name']; ?> - Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="images/<?php echo $row['image']; ?>" class="medicine-modal-thumb" alt="medicine">
                                <p class="text-muted"><?php echo $row['description'] ? $row['description'] : "No description available."; ?></p>
                                <h4 class="text-success">Price: Rs.<?php echo number_format($row['price'], 2); ?></h4>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<div class='col-12 text-center'><p class='alert alert-warning mb-0'>No medicines available.</p></div>";
                }
                ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>