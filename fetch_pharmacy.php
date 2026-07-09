<?php
include 'config.php';
session_start();

// --- STOCK ADJUSTMENT LOGIC (Kept as requested) ---
if(isset($_POST['adjust_stock'])){
    $product_id = (int)$_POST['product_id'];
    $action = $_POST['adjust_stock']; 
    
    if($action == 'plus'){
        mysqli_query($conn, "UPDATE `products` SET stock = stock + 1 WHERE id = '$product_id'") or die('query failed');
    } elseif($action == 'minus'){
        mysqli_query($conn, "UPDATE `products` SET stock = GREATEST(0, stock - 1) WHERE id = '$product_id'") or die('query failed');
    }
    header('location:fetch_pharmacy.php');
    exit();
}

// Update Logic (In-page modal)
if(isset($_POST['update_product'])){
    $update_id = (int)$_POST['update_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (float)$_POST['price'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = (int)$_POST['stock'];

    mysqli_query($conn, "UPDATE `products` SET name = '$name', price = '$price', description = '$description', stock = '$stock' WHERE id = '$update_id'") or die('query failed');

    if(isset($_FILES['image']) && !empty($_FILES['image']['name'])){
        $image = basename($_FILES['image']['name']);
        $image_tmp_name = $_FILES['image']['tmp_name'];
        move_uploaded_file($image_tmp_name, 'images/' . $image);
        mysqli_query($conn, "UPDATE `products` SET image = '$image' WHERE id = '$update_id'") or die('query failed');
    }

    header('location:fetch_pharmacy.php');
    exit();
}

// Delete Logic
if(isset($_GET['delete'])){
    $delete_id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
    header('location:fetch_pharmacy.php');
    exit();
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_product = null;
if($edit_id > 0){
    $edit_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$edit_id' LIMIT 1");
    if($edit_query && mysqli_num_rows($edit_query) > 0){
        $edit_product = mysqli_fetch_assoc($edit_query);
    }
}

$total_products = $conn->query("SELECT id FROM products")->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Pharmacy - HMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
    <link rel='stylesheet' href='admin-sidebar.css'>
    <style>
        :root {
            --bg-dark: #072a43;
            --shadow: 0 10px 28px rgba(7, 42, 67, 0.1);
        }
        body { margin: 0; font-family: "Segoe UI", sans-serif; background: #eef4f8; min-height: 100vh; }
        .main-content { padding: 28px; }
        .hero { background: #fff; border-radius: 20px; padding: 22px; box-shadow: var(--shadow); display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .hero h1 { margin: 0; font-size: 1.65rem; color: #0b7fab; font-weight: 700; }
        .table-panel { background: #fff; border-radius: 16px; padding: 20px; box-shadow: var(--shadow); }
        
        /* Modal Backdrop */
        .page-shell.blur-active { filter: blur(4px); pointer-events: none; }
        .edit-overlay { position: fixed; inset: 0; background: rgba(7, 25, 39, 0.4); display: flex; align-items: center; justify-content: center; z-index: 2000; }
        .edit-modal { width: 100%; max-width: 600px; background: #fff; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); overflow: hidden; }
        .edit-modal-head { display: flex; justify-content: space-between; padding: 15px; background: #f8fbff; border-bottom: 1px solid #eee; }

        /* Stock Control Styling from your Image 2 */
        .stock-manager { display: flex; align-items: center; gap: 10px; justify-content: center; }
        .stock-btn { border: 1px solid #d2e1ee; background: #fff; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .stock-btn:hover { background: #eef7ff; border-color: #0b7fab; }
        .stock-count { font-weight: 700; font-size: 1.1rem; min-width: 30px; text-align: center; }

        .table thead { background-color: #f8f9fa !important; color: #000 !important; border-bottom: 2px solid #dee2e6; }
        .med-img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
    </style>
</head>

<body>
    <?php $admin_active = 'pharmacy'; include 'admin_sidebar.php'; ?>
    
    <div class="page-shell <?php echo $edit_product ? 'blur-active' : ''; ?>">
        <div class="main-content">
            <section class="hero">
                <div>
                    <h1><i class="fas fa-capsules"></i> Pharmacy Inventory</h1>
                    <p>Total Items: <?php echo $total_products; ?></p>
                </div>
                <a href="admin_add_medicine.php" class="btn btn-primary hms-btn btn-primary-action"><i class="fas fa-plus-circle"></i> Add Medicine</a>
            </section>

            <div class="table-panel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="pharmacyTable">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Medicine Name</th>
                                <th>Price</th>                                
                                <th>Description</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $select_products = mysqli_query($conn, "SELECT * FROM `products` ORDER BY id DESC");
                            while($fetch_products = mysqli_fetch_assoc($select_products)){
                            ?>
                            <tr>
                                <td><img src="images/<?php echo htmlspecialchars($fetch_products['image']); ?>" class="med-img"></td>
                                <td><strong><?php echo htmlspecialchars($fetch_products['name']); ?></strong></td>
                                <td>&#8377;<?php echo number_format($fetch_products['price'], 2); ?></td>
                                
                               

                                <td><small class="text-muted"><?php echo htmlspecialchars($fetch_products['description']); ?></small></td>
                                <td class="text-center">
                                    <a href="fetch_pharmacy.php?edit=<?php echo $fetch_products['id']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="fetch_pharmacy.php?delete=<?php echo $fetch_products['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this medicine?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if($edit_product): ?>
    <div class="edit-overlay">
        <div class="edit-modal">
            <div class="edit-modal-head">
                <h5><i class="fas fa-edit"></i> Edit Pharmacy Item</h5>
                <a href="fetch_pharmacy.php" class="btn btn-sm btn-light border">Close</a>
            </div>
            <div class="p-4">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="update_id" value="<?php echo $edit_product['id']; ?>">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Medicine Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($edit_product['name']); ?>" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" value="<?php echo $edit_product['stock']; ?>" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Price (INR)</label>
                            <input type="number" step="0.01" name="price" value="<?php echo $edit_product['price']; ?>" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?php echo htmlspecialchars($edit_product['description']); ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Update Image (Optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" name="update_product" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="fetch_pharmacy.php" class="btn btn-light border w-100">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src='admin-sidebar.js'></script>
</body>
</html>