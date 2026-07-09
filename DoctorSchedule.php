<?php
include 'config.php';
session_start();

// Ensure doctor is logged in
if (!isset($_SESSION['doctor_id'])) {
    header('location:Doctor_Login.php');
    exit();
}

$current_doctor_id = (int)$_SESSION['doctor_id'];

if (isset($_POST['save_schedule'])) {
    $day = $_POST['day'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];

    $startTimestamp = strtotime($start);
    $endTimestamp = strtotime($end);
    $durationMinutes = ($endTimestamp - $startTimestamp) / 60;

    // 1. Basic validation: End time must be later than start time
    if ($endTimestamp <= $startTimestamp) {
        echo "<script>alert('Error: End Time must be later than Start Time!');</script>";
    } 
    // 2. NEW FEATURE: Minimum 30 Minutes validation
    elseif ($durationMinutes < 30) {
        echo "<script>alert('Error: Minimum slot time duration is 30 minutes. Please adjust your end time.');</script>";
    }
    else {
        // 3. Overlap validation logic
        $check_sql = "SELECT end_time FROM doctor_schedule 
                      WHERE doctor_id = ? AND available_day = ? 
                      AND (
                          (start_time <= ? AND end_time > ?) OR 
                          (start_time < ? AND end_time >= ?) OR 
                          (? <= start_time AND ? > start_time)
                      ) LIMIT 1";
        
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('isssssss', $current_doctor_id, $day, $start, $start, $end, $end, $start, $end);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            $suggested_time = date('g:i A', strtotime($row['end_time']));
            echo "<script>alert('This slot time is already declared or overlaps with an existing slot. Please choose a time after $suggested_time.');</script>";
        } else {
            // 4. No overlap and valid duration, proceed to save
            $stmt = $conn->prepare("INSERT INTO doctor_schedule (doctor_id, available_day, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $current_doctor_id, $day, $start, $end);
            if ($stmt->execute()) {
                echo "<script>alert('Schedule saved successfully!'); window.location.href='DoctorSchedule.php';</script>";
                exit();
            }
        }
    }
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM doctor_schedule WHERE id = $id AND doctor_id = $current_doctor_id");
    header('Location: DoctorSchedule.php');
    exit();
}

// Fetch Doctor Name
$doctor_name = 'Doctor';
$doctor_result = mysqli_query($conn, "SELECT DoctorName FROM add_doctor WHERE id = '$current_doctor_id' LIMIT 1");
if ($doctor_result && ($doctor_row = mysqli_fetch_assoc($doctor_result))) {
    $doctor_name = $doctor_row['DoctorName'];
}

// Fetch current schedule
$stmt = $conn->prepare("SELECT * FROM doctor_schedule WHERE doctor_id = ? ORDER BY FIELD(available_day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') ASC, start_time ASC");
$stmt->bind_param('i', $current_doctor_id);
$stmt->execute();
$sched = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Schedule - HMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --bg-dark: #072a43; --bg-mid: #0e3b5f; --text-dark: #12212e; --text-muted: #557085; --shadow: 0 10px 28px rgba(7,42,67,.1);}
        *{box-sizing:border-box}
        body{margin:0;font-family:"Segoe UI",Tahoma,Geneva,Verdana,sans-serif;color:var(--text-dark);background:radial-gradient(circle at 15% 5%,#d8f3ff 0%,transparent 34%),radial-gradient(circle at 92% 92%,#dff0ff 0%,transparent 28%),#eef4f8;min-height:100vh}
        .mobile-topbar{display:none;align-items:center;justify-content:space-between;padding:14px 16px;background:linear-gradient(120deg,var(--bg-dark),var(--bg-mid));color:#fff;position:sticky;top:0;z-index:1100}
        .menu-btn{border:0;background:rgba(255,255,255,.2);color:#fff;width:40px;height:40px;border-radius:10px;cursor:pointer}
        .layout{display:flex;min-height:100vh}
        .sidebar{width:280px;background:linear-gradient(180deg,var(--bg-dark),#062338);color:#dce9f4;padding:24px 16px;position:fixed;top:0;left:0;bottom:0;overflow-y:auto;z-index:1000;box-shadow:4px 0 20px rgba(0,0,0,.16);transition:transform .3s ease}
        .sidebar-header{display:flex;align-items:center;gap:12px;padding:0 10px 20px;border-bottom:1px solid rgba(255,255,255,.14)}
        .sidebar-header h3{margin:0;color:#fff}
        .sidebar-menu{list-style:none;padding:18px 0 0;margin:0}
        .sidebar-menu li{margin-bottom:6px}
        .sidebar-menu a{display:flex;align-items:center;gap:10px;padding:11px 12px;border-radius:12px;color:#dce9f4;text-decoration:none;transition:.2s ease}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.13);color:#fff}
        .has-submenu .dropdown-icon{margin-left:auto;transition:transform .3s ease}
        .has-submenu:hover>a .dropdown-icon{transform:rotate(180deg)}
        .has-submenu .submenu{list-style:none;margin:6px 0 0;padding:0 0 0 12px;max-height:0;overflow:hidden;transition:max-height .35s ease}
        .has-submenu:hover .submenu{max-height:220px}
        .main-content{flex:1;margin-left:280px;padding:24px}
        .panel{background:#fff;border:1px solid #dbe9f5;border-radius:16px;box-shadow:var(--shadow);padding:20px;margin-bottom:14px}
        .panel h1,.panel h2{margin:0 0 14px}
        .form-grid{display:grid;grid-template-columns:1.2fr 1fr 1fr auto;gap:10px;align-items:end}
        label{display:block;font-weight:600;margin-bottom:6px;color:#35566e;font-size:.92rem}
        select,input[type="time"]{width:100%;border:1px solid #cadbeb;border-radius:10px;padding:9px 10px;background:#fbfdff}
        .btn-primary{border:0;border-radius:10px;padding:10px 14px;background:#0b7fab;color:#fff;cursor:pointer;font-weight:600}
        table{width:100%;border-collapse:collapse}
        th,td{text-align:left;padding:10px 8px;border-bottom:1px solid #e8eff6}
        th{color:#557085;font-weight:600;font-size:.92rem}
        .text-center{text-align:center}
        .empty{padding:14px;border:1px dashed #c9d8e6;border-radius:12px;color:var(--text-muted);text-align:center;background:#fbfdff}
        .delete-link{color:#dc3545;text-decoration:none}
        @media (max-width:991px){.mobile-topbar{display:flex}.sidebar{transform:translateX(-100%)}.sidebar.show{transform:translateX(0)}.main-content{margin-left:0;width:100%;padding:16px}.form-grid{grid-template-columns:1fr}table{min-width:520px}.table-wrap{overflow-x:auto}}
    </style>
</head>
<body>
    <div class="mobile-topbar">
        <strong>HMS Doctor Panel</strong>
        <button class="menu-btn" id="menuBtn"><i class="fas fa-bars"></i></button>
    </div>
    <div class="layout">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <img src="logo.png" width="40" height="40" alt="Logo">
                <h3>HMS</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="Doctor_dashboard.php"><i class="fas fa-home"></i>Dashboard</a></li>
                <li><a href="Doctor_profile.php"><i class="fa fa-user-md"></i>My Profile</a></li>
                <li class="has-submenu">
                    <a href="javascript:void(0)"><i class="fas fa-calendar-alt"></i>Appointments <i class="fas fa-caret-down dropdown-icon"></i></a>
                    <ul class="submenu">
                        <li><a href="DoctorPersonalAppointments.php"><i class="fas fa-list"></i>View Appointments</a></li>
                        <li><a href="DoctorsPersonalPatient.php"><i class="fas fa-user-injured"></i>My Patients</a></li>
                    </ul>
                </li>
                <li><a href="DoctorSchedule.php" class="active"><i class="fas fa-clock"></i>Doctor Schedule</a></li>
                <li><a href="Doctor_dashboard.php?logout=true"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <section class="panel">
                <h1>Manage Availability: Dr. <?php echo htmlspecialchars($doctor_name); ?></h1>
                <form method="POST" class="form-grid" id="scheduleForm">
                    <div>
                        <label>Day</label>
                        <select name="day" required>
                            <option>Monday</option>
                            <option>Tuesday</option>
                            <option>Wednesday</option>
                            <option>Thursday</option>
                            <option>Friday</option>
                            <option>Saturday</option>
                            <option>Sunday</option>
                        </select>
                    </div>
                    <div>
                        <label>Start Time</label>
                        <input type="time" name="start_time" id="start_time" required>
                    </div>
                    <div>
                        <label>End Time</label>
                        <input type="time" name="end_time" id="end_time" required>
                    </div>
                    <div>
                        <button type="submit" name="save_schedule" class="btn-primary">Add Slot</button>
                    </div>
                </form>
            </section>

            <section class="panel">
                <h2>My Current Slots</h2>
                <?php if ($sched && $sched->num_rows > 0) { ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Time Range</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $sched->fetch_assoc()) { ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['available_day']); ?></strong></td>
                                        <td><?php echo date('g:i A', strtotime($row['start_time'])); ?> - <?php echo date('g:i A', strtotime($row['end_time'])); ?></td>
                                        <td class="text-center">
                                            <a href="?delete_id=<?php echo (int)$row['id']; ?>" class="delete-link" onclick="return confirm('Delete this slot?')">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="empty">You have not added any availability slots yet.</div>
                <?php } ?>
            </section>
        </main>
    </div>

    <script>
        // Menu Toggle
        const menuBtn=document.getElementById('menuBtn');
        const sidebar=document.getElementById('sidebar');
        if(menuBtn){
            menuBtn.addEventListener('click',function(){
                sidebar.classList.toggle('show');
            });
        }

        // Frontend validation for 30-minute minimum
        document.getElementById('scheduleForm').addEventListener('submit', function(e) {
            const start = document.getElementById('start_time').value;
            const end = document.getElementById('end_time').value;

            if (start && end) {
                const startDate = new Date(`2000-01-01T${start}`);
                const endDate = new Date(`2000-01-01T${end}`);
                
                const diffMs = endDate - startDate;
                const diffMins = diffMs / (1000 * 60);

                if (diffMins < 30) {
                    e.preventDefault();
                    alert("Minimum slot time is 30 minutes. Please adjust your end time.");
                }
            }
        });
    </script>
</body>
</html>