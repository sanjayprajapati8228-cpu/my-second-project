<?php
session_start();

$admin_user = 'admin';
$admin_pass = 'admin123';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $UserId = $_POST['userid'];
    $password = $_POST['password'];

    if ($UserId == $admin_user && $password == $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: AdminDashboard.php");
        exit();
    } else {
        echo "<script>alert('Invalid User ID or Password'); window.location.href='adminlogin.php';</script>";
        exit();
    }
}
?>
