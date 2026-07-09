<?php
session_start();
include 'config.php';

if (!isset($_SESSION['doctor_id'])) {
    header('location:Doctor_Login.php');
    exit();
}

$current_doctor_id = (int)$_SESSION['doctor_id'];

if (isset($_GET['id']) && isset($_GET['action'])) {
    $appointment_id = (int)$_GET['id'];
    $action = $_GET['action'];

    $info_query = $conn->prepare("SELECT a.*, u.Email as PatientEmail, d.DoctorName FROM user_appointments a JOIN user_registration1 u ON a.patient_id = u.id JOIN add_doctor d ON a.doctor_id = d.id WHERE a.id = ? AND a.doctor_id = ?");
    $info_query->bind_param('ii', $appointment_id, $current_doctor_id);
    $info_query->execute();
    $details = $info_query->get_result()->fetch_assoc();

    if ($details) {
        $patient_email = $details['PatientEmail'];
        $doctor_name = $details['DoctorName'];
        $date = $details['appointment_Date'];
        $time = $details['appointment_Time'];

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: HMS Appointments <gujaratijeel15@gmail.com>\r\n";
        $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";

        if ($action === 'confirm') {
            $stmt = $conn->prepare("UPDATE user_appointments SET status = 'Confirmed' WHERE id = ?");
            $stmt->bind_param('i', $appointment_id);
            $stmt->execute();

            $subject = 'Appointment Confirmed - HMS';
            $message = "<html><head><meta charset='UTF-8'><title>Appointment Confirmed</title></head><body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'><table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'><table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'><tr><td style='background:linear-gradient(90deg,#1d6b3f,#2d9f5a);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Appointment Confirmed</td></tr><tr><td style='padding:24px;line-height:1.7;'><p>Your appointment with <strong>Dr. $doctor_name</strong> is confirmed.</p><p><strong>Date:</strong> $date<br><strong>Time:</strong> $time</p></td></tr><tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Appointments Team</td></tr></table></td></tr></table></body></html>";
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM user_appointments WHERE id = ?");
            $stmt->bind_param('i', $appointment_id);
            $stmt->execute();

            $subject = 'Appointment Cancelled - HMS';
            $message = "<html><head><meta charset='UTF-8'><title>Appointment Cancelled</title></head><body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'><table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'><table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'><tr><td style='background:linear-gradient(90deg,#8b1f1f,#bb3e3e);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Appointment Cancelled</td></tr><tr><td style='padding:24px;line-height:1.7;'><p>We regret to inform you that your appointment with <strong>Dr. $doctor_name</strong> on <strong>$date</strong> has been cancelled.</p></td></tr><tr><td style='padding:14px 24px;background:#fdf3f3;color:#8a4a4a;font-size:12px;'>HMS Appointments Team</td></tr></table></td></tr></table></body></html>";
        }

        if (isset($subject) && isset($message)) {
            mail($patient_email, $subject, $message, $headers);
        }

        echo "<script>alert('Action completed and email sent to patient.'); window.location.href='DoctorPersonalAppointments.php';</script>";
        exit();
    }
}

$doctor_name = 'Doctor';
$select_doc = mysqli_query($conn, "SELECT DoctorName FROM add_doctor WHERE id = '$current_doctor_id'");
if ($select_doc && ($fetch_doc = mysqli_fetch_assoc($select_doc))) {
    $doctor_name = $fetch_doc['DoctorName'];
}

$sql = "SELECT id, appointment_Date, appointment_Time, Phone, Name, status, patient_report FROM user_appointments WHERE doctor_id = ? ORDER BY appointment_Date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $current_doctor_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Doctor Appointments</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="hms-ui-consistency.css"><link rel="stylesheet" href="hms-theme.css">
<style>:root { --bg-dark: #072a43; --bg-mid: #0e3b5f; --text-dark: #12212e; --text-muted: #557085; --shadow: 0 10px 28px rgba(7,42,67,.1);}*{box-sizing:border-box}body{margin:0;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;color:var(--text-dark);background:radial-gradient(circle at 15% 5%,#d8f3ff 0%,transparent 34%),radial-gradient(circle at 92% 92%,#dff0ff 0%,transparent 28%),#eef4f8;min-height:100vh}.mobile-topbar{display:none;align-items:center;justify-content:space-between;padding:14px 16px;background:linear-gradient(120deg,var(--bg-dark),var(--bg-mid));color:#fff;position:sticky;top:0;z-index:1100}.menu-btn{border:0;background:rgba(255,255,255,.2);color:#fff;width:40px;height:40px;border-radius:10px;cursor:pointer}.layout{display:flex;min-height:100vh}.sidebar{width:280px;background:linear-gradient(180deg,var(--bg-dark),#062338);color:#dce9f4;padding:24px 16px;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:1000;box-shadow:4px 0 20px rgba(0,0,0,.16);transition:transform .3s ease}.sidebar-header{display:flex;align-items:center;gap:12px;padding:0 10px 20px;border-bottom:1px solid rgba(255,255,255,.14)}.sidebar-header h3{margin:0;color:#fff}.sidebar-menu{list-style:none;padding:18px 0 0;margin:0}.sidebar-menu li{margin-bottom:6px}.sidebar-menu a{display:flex;align-items:center;gap:10px;padding:11px 12px;border-radius:12px;color:#dce9f4;text-decoration:none;transition:.2s ease}.sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.13);color:#fff}.has-submenu .dropdown-icon{margin-left:auto;transition:transform .3s ease}.has-submenu:hover>a .dropdown-icon{transform:rotate(180deg)}.has-submenu .submenu{list-style:none;margin:6px 0 0;padding:0 0 0 12px;max-height:0;overflow:hidden;transition:max-height .35s ease}.has-submenu:hover .submenu{max-height:220px}.has-submenu .submenu a{font-size:.92rem;color:#bdd2e4;padding:9px 12px}.main-content{flex:1;margin-left:280px;padding:24px}.panel{background:#fff;border:1px solid #dbe9f5;border-radius:16px;box-shadow:var(--shadow);padding:20px}.panel h1{margin:0 0 16px;font-size:1.4rem}.appointment-list{list-style:none;margin:0;padding:0}.appointment-item{border:1px solid #e2edf6;border-radius:12px;background:#fbfdff;padding:14px;margin-bottom:12px}.appointment-top{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}.status-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:.78rem;font-weight:700}.status-pending{background:#fff4db;color:#9b6400}.status-confirmed{background:#ddf5e7;color:#14663f}.meta{margin-top:8px;color:#557085;font-size:.92rem}.actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap}.btn-action{display:inline-flex;align-items:center;gap:7px;border-radius:10px;text-decoration:none;padding:8px 11px;font-size:.88rem;font-weight:600}.btn-confirm{background:#198754;color:#fff}.btn-delete{background:#dc3545;color:#fff}.btn-back{background:#eef5fb;color:#14405e}.empty{padding:14px;border:1px dashed #c9d8e6;border-radius:12px;color:var(--text-muted);text-align:center;background:#fbfdff}@media (max-width:991px){.mobile-topbar{display:flex}.sidebar{transform:translateX(-100%)}.sidebar.show{transform:translateX(0)}.main-content{margin-left:0;width:100%;padding:16px}}</style>
    <link rel="stylesheet" href="css/buttons.css"></head>
<body><div class="mobile-topbar"><strong>HMS Doctor Panel</strong><button class="menu-btn" id="menuBtn" aria-label="Open menu"><i class="fas fa-bars"></i></button></div><div class="layout"><aside class="sidebar" id="sidebar"><div class="sidebar-header"><img src="logo.png" width="40" height="40" alt="Logo"><h3>HMS</h3></div><ul class="sidebar-menu"><li><a href="Doctor_dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li><li><a href="Doctor_profile.php"><i class="fa fa-user-md"></i>My Profile</a></li><li class="has-submenu"><a href="javascript:void(0)" class="active"><i class="fas fa-calendar-alt"></i>Appointments <i class="fas fa-caret-down dropdown-icon"></i></a><ul class="submenu"><li><a href="DoctorPersonalAppointments.php" class="active"><i class="fas fa-list"></i>View Appointments</a></li><li><a href="DoctorsPersonalPatient.php"><i class="fas fa-user-injured"></i>My Patients</a></li></ul></li><li><a href="DoctorSchedule.php"><i class="fas fa-clock"></i>Doctor Schedule</a></li><li><a href="Doctor_dashboard.php?logout=true" class="btn-danger-action"><i class="fas fa-sign-out-alt"></i>Logout</a></li></ul></aside><main class="main-content"><section class="panel"><h1>Appointments for Dr. <?php echo htmlspecialchars($doctor_name); ?></h1><?php if ($result && $result->num_rows > 0) { ?><ul class="appointment-list"><?php while ($row = $result->fetch_assoc()) { ?><?php $status = $row['status'] ?: 'Pending'; $status_class = ($status === 'Confirmed') ? 'status-confirmed' : 'status-pending'; ?><li class="appointment-item"><div class="appointment-top"><strong>Patient: <?php echo htmlspecialchars($row['Name']); ?></strong><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status); ?></span></div><div class="meta">Date: <?php echo date('d M Y', strtotime($row['appointment_Date'])); ?> | Time: <?php echo date('h:i A', strtotime($row['appointment_Time'])); ?><br>Phone: <?php echo htmlspecialchars($row['Phone']); ?></div><div class="actions"><?php if (!empty($row['patient_report'])) { $report_file = basename($row['patient_report']); ?><a href="uploads/<?php echo rawurlencode($report_file); ?>" class="btn-action btn-back" target="_blank" rel="noopener"><i class="fas fa-file-medical"></i>Report</a><?php } ?><?php if ($status === 'Pending') { ?><a href="DoctorPersonalAppointments.php?id=<?php echo (int)$row['id']; ?>&action=confirm" class="btn-action btn-confirm"><i class="fas fa-check"></i>Confirm</a><?php } ?><a href="DoctorPersonalAppointments.php?id=<?php echo (int)$row['id']; ?>&action=delete" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to cancel and remove this appointment?')"><i class="fas fa-trash-alt"></i>Delete</a></div></li><?php } ?></ul><?php } else { ?><div class="empty">You do not have any appointments scheduled.</div><?php } ?><div style="margin-top:14px;"><a href="Doctor_dashboard.php" class="btn-action btn-back btn-navigation"><i class="fas fa-arrow-left"></i>Back to Dashboard</a></div></section></main></div><script>const menuBtn=document.getElementById('menuBtn');const sidebar=document.getElementById('sidebar');if(menuBtn){menuBtn.addEventListener('click',function(){sidebar.classList.toggle('show');});}</script></body></html>





