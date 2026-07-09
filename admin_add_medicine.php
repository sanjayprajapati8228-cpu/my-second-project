<?php
include 'config.php';
session_start();

// Optional: Add admin check here
// if($_SESSION['user_role'] != 'admin'){ header('location:UserLogin.php'); }

if(isset($_POST['add_medicine'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = (int)$_POST['stock']; // New Stock Field
    
    // Handle Image Upload
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'images/' . $image;

    if(!empty($name) && !empty($price)){
        // Updated Query to include stock
        $insert_query = mysqli_query($conn, "INSERT INTO `products` (name, price, description, image, stock) 
                        VALUES ('$name', '$price', '$description', '$image', '$stock')") or die('Query failed');

        if($insert_query){
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Medicine added successfully!';
        }else{
            $message[] = 'Could not add the medicine.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
    <title>Admin - Add Medicine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        }

        .main-content {
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
            color: #0b7fab;
            font-weight: 700;
        }

        .hero p {
            margin: 8px 0 0;
            color: var(--text-muted);
        }

        .form-panel {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #e2edf5;
            padding: 22px;
            box-shadow: var(--shadow);
            max-width: 760px;
            width: 100%;
            margin: 0 auto;
        }

        .form-panel h3 {
            font-size: 1.08rem;
            margin: 0 0 14px;
        }

        .form-label {
            font-weight: 600;
            color: #1c3f58;
        }

        .form-control,
        .input-group-text {
            border-radius: 10px;
            border: 1px solid #d2e1ee;
        }

        .form-control:focus {
            border-color: #8ab7d6;
            box-shadow: 0 0 0 .2rem rgba(11, 127, 171, 0.14);
        }

        .actions {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 16px;
            }
            .hero {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
    <link rel='stylesheet' href='admin-sidebar.css'>
</head>
<body>
    <?php $admin_active = 'add_medicine'; include 'admin_sidebar.php'; ?>

    <div class="main-content">
        <section class="hero">
            <div>
                <h1><i class="fas fa-pills"></i> Add Medicine</h1>
                <p>Create pharmacy item with price, description, stock and product image.</p>
            </div>
            <a href="fetch_pharmacy.php" class="btn btn-outline-secondary hms-btn btn-secondary-action">
                <i class="fas fa-list"></i> View Pharmacy
            </a>
        </section>

        <section class="form-panel">
            <h3>Medicine Information</h3>

            <?php
            if(isset($message)){
               foreach($message as $msg){
                  echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            '.$msg.'
                            <button type="button" class="btn-close btn-secondary-action" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
               }
            }
            ?>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Medicine Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Paracetamol" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Price (INR)</label>
                        <div class="input-group">
                            <span class="input-group-text">&#8377;</span>
                            <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock" min="0" class="form-control" placeholder="Quantity in stock" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter medicine details, usage, etc."></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Medicine Image</label>
                    <input type="file" name="image" class="form-control" accept="image/jpg, image/jpeg, image/png" required>
                    <small class="text-muted">Allowed: JPG, JPEG, PNG</small>
                </div>

                <div class="actions">
                    <a href="AdminDashboard.php" class="btn-navigation">&larr; Go Back</a>
                    <button type="submit" name="add_medicine" class="btn btn-primary-action">
                        <i class="fas fa-plus-circle"></i> Add to Pharmacy
                    </button>
                </div>
            </form>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <script src='admin-sidebar.js'></script>
</body>
</html>