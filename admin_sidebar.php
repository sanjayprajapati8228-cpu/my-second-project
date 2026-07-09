<?php
if (!isset($admin_active)) {
    $admin_active = '';
}

$is_doctors = in_array($admin_active, ['add_doctor', 'doctors'], true);
$is_pharmacy = in_array($admin_active, ['add_medicine', 'pharmacy'], true);
?>
<div class="mobile-topbar admin-mobile-topbar">
    <strong>HMS Admin</strong>
    <button class="menu-btn" id="adminMenuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button>
</div>

<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <img src="logo.png" width="40" height="40" alt="Logo">
        <h3>HMS</h3>
    </div>

    <ul class="sidebar-menu">
        <li><a href="AdminDashboard.php" class="<?php echo $admin_active === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-home"></i>Dashboard</a></li>

        <li class="has-submenu <?php echo $is_doctors ? 'open' : ''; ?>">
            <a href="javascript:void(0)" class="<?php echo $is_doctors ? 'active' : ''; ?>" data-submenu-toggle="doctors" aria-expanded="<?php echo $is_doctors ? 'true' : 'false'; ?>"><i class="fas fa-user-md"></i>Manage Doctors <i class="fas fa-caret-down dropdown-icon"></i></a>
            <ul class="submenu <?php echo $is_doctors ? 'submenu-open' : ''; ?>" data-submenu="doctors">
                <li><a href="Add_Doctor.php" class="<?php echo $admin_active === 'add_doctor' ? 'active' : ''; ?>">Add Doctors</a></li>
                <li><a href="FeatchDoctors.php" class="<?php echo $admin_active === 'doctors' ? 'active' : ''; ?>">View Doctors</a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $is_pharmacy ? 'open' : ''; ?>">
            <a href="javascript:void(0)" class="<?php echo $is_pharmacy ? 'active' : ''; ?>" data-submenu-toggle="pharmacy" aria-expanded="<?php echo $is_pharmacy ? 'true' : 'false'; ?>"><i class="fas fa-pills"></i>Pharmacy <i class="fas fa-caret-down dropdown-icon"></i></a>
            <ul class="submenu <?php echo $is_pharmacy ? 'submenu-open' : ''; ?>" data-submenu="pharmacy">
                <li><a href="admin_add_medicine.php" class="<?php echo $admin_active === 'add_medicine' ? 'active' : ''; ?>">Add Pharmacy</a></li>
                <li><a href="fetch_pharmacy.php" class="<?php echo $admin_active === 'pharmacy' ? 'active' : ''; ?>">View Pharmacy</a></li>
            </ul>
        </li>

        <li><a href="FeatchUserR.php" class="<?php echo $admin_active === 'users' ? 'active' : ''; ?>"><i class="fas fa-procedures"></i>Manage Users</a></li>
        <li><a href="admin_orders.php" class="<?php echo $admin_active === 'orders' ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i>Manage Orders</a></li>
        <li><a href="FeatchAppointments.php" class="<?php echo $admin_active === 'appointments' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i>Manage Appointments</a></li>
        <li><a href="FetchContect.php" class="<?php echo $admin_active === 'feedback' ? 'active' : ''; ?>"><i class="fas fa-comments"></i>Feedback</a></li>
        <li><a href="Index2.html" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
    </ul>
</aside>
