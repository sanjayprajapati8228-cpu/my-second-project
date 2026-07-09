<?php
include 'config.php';
session_start();

$update_id = $_GET['update'];

if(isset($_POST['update_product'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = (float)$_POST['price'];
    if($price < 0){
        echo "<script>alert('Price cannot be negative.');history.back();</script>";
        exit();
    }
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    mysqli_query($conn, "UPDATE `products` SET name = '$name', price = '$price', description = '$description' WHERE id = '$update_id'") or die('query failed');

    // Handle Image Update (Optional)
    $image = $_FILES['image']['name'];
    if(!empty($image)){
        $image_tmp_name = $_FILES['image']['tmp_name'];
        move_uploaded_file($image_tmp_name, 'images/'.$image);
        mysqli_query($conn, "UPDATE `products` SET image = '$image' WHERE id = '$update_id'");
    }

    header('location:fetch_pharmacy.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Medicine</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body class="hms-panel">
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Toggle sidebar">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="logo.png" width="40" height="40" alt="Logo">
            <h3>HMS</h3>
        </div>
        <ul class="sidebar-menu">
            <li><a href="AdminDashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="has-submenu">
                <a href="#"><i class="fas fa-user-md"></i> Manage Doctors <i class="fas fa-caret-down dropdown-icon"></i></a>
                <ul class="submenu">
                    <li><a href="Add_Doctor.php">Add Doctors</a></li>
                    <li><a href="FeatchDoctors.php">View Doctors</a></li>
                </ul>
            </li>
            <li class="has-submenu">
                <a href="#" class="active"><i class="fas fa-pills"></i> Pharmacy <i class="fas fa-caret-down dropdown-icon"></i></a>
                <ul class="submenu">
                    <li><a href="admin_add_medicine.php">Add Pharmacy</a></li>
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
        <h3 class="page-title"><i class="fas fa-capsules"></i> Update Medicine</h3>
        <div class="form-container" style="max-width: 760px;">
                <?php
                $update_query = mysqli_query($conn, "SELECT * FROM `products` WHERE id = '$update_id'");
                $fetch_data = mysqli_fetch_assoc($update_query);
                ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Medicine Name</label>
                        <input type="text" name="name" value="<?php echo $fetch_data['name']; ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price (INR)</label>
                        <input type="number" step="0.01" min="0" name="price" value="<?php echo $fetch_data['price']; ?>" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?php echo $fetch_data['description']; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Update Image (Leave blank to keep current)</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="update_product" class="btn-primary-action w-100">Save Changes</button>
                        <a href="fetch_pharmacy.php" class="btn-navigation w-100">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="hms-panel.js"></script>
</body>
</html>





