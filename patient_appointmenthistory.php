
<?php
include 'config.php';
session_start();

// Check if patient is logged in
if(!isset($_SESSION['user_id'])){
    header('location:UserLogin.php');
    exit();
}

$patient_id = $_SESSION['user_id'];

// --- DELETE LOGIC ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    // Ensure the user can only delete their own appointments for security
    $delete_stmt = $conn->prepare("DELETE FROM user_appointments WHERE id = ? AND patient_id = ?");
    $delete_stmt->bind_param("ii", $delete_id, $patient_id);
    
    if ($delete_stmt->execute()) {
        echo "<script>alert('Appointment deleted successfully'); window.location.href='patient_appointmenthistory.php';</script>";
    } else {
        echo "<script>alert('Error deleting appointment');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointment History - HMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            background: radial-gradient(circle at 14% 2%, #d9edff 0%, transparent 34%), #edf4fa;
            margin: 0;
            color: #173247;
        }
        
        .container { margin-top: 30px; }

        .table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #dbe7f1;
            box-shadow: 0 12px 30px rgba(8, 46, 72, 0.08);
        }

        /* Dark Table Header matching admin style */
        .table thead th {
            background-color: #0d3d5a !important;
            color: white !important;
            padding: 15px;
            border: none;
            font-size: 14px;
            text-transform: uppercase;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Status Colors */
        .bg-pending { 
			background-color: #fff3cd; 
			color: #856404; 
		}
        .bg-confirmed {
			background-color: #d1e7dd; 
			color: #0f5132; 
		}
        .bg-cancelled { 
			background-color: #f8d7da; 
			color: #842029; 
		}

        .page-title {
            color: #0d3d5a;
            font-weight: bold;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-delete {
            color: #dc3545;
            transition: 0.3s;
            background: none;
            border: none;
        }
        .btn-delete:hover {
            color: #a71d2a;
            transform: scale(1.1);
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>

<nav class="navbar navbar-dark mb-4" style="background: linear-gradient(120deg, #062338, #0f4568);">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">HMS Patient Portal</span>
        <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
    </div>
</nav>

<div class="container">
    <h3 class="page-title"><i class="fas fa-history"></i> My Appointment History</h3>

    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Appt ID</th>
                        <th>Doctor Name</th>
                        <th>Scheduled Date</th>
                        <th>Time Slot</th>
                        <th>Fees Paid</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch appointments only for this specific patient
                    $query = "SELECT a.*, d.DoctorName 
                              FROM user_appointments a 
                              LEFT JOIN add_doctor d ON a.doctor_id = d.id 
                              WHERE a.patient_id = ? 
                              ORDER BY a.id DESC";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $patient_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $status = $row['status'] ?? 'Pending';
                            $badge_class = 'bg-pending';
                            if($status == 'Confirmed') $badge_class = 'bg-confirmed';
                            if($status == 'Cancelled') $badge_class = 'bg-cancelled';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $row['id']; ?></strong></td>
                        <td><i class="fas fa-user-md me-2 text-primary"></i>Dr. <?php echo htmlspecialchars($row['DoctorName']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['appointment_Date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['appointment_Time']); ?></td>
                        <td>&#8377;<?php echo htmlspecialchars($row['cash']); ?></td>
                        <td><span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
                        <td class="text-center">
                            <a href="javascript:void(0)" 
                               onclick="confirmDelete(<?php echo $row['id']; ?>)" 
                               class="btn-danger-action hms-icon-btn">
                                <i class="fas fa-trash-alt fa-lg"></i>
                            </a>
                        </td>
                    </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center py-5 text-muted'>You have no appointment history.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this appointment record?")) {
            window.location.href = "patient_appointmenthistory.php?delete_id=" + id;
        }
    }
</script>

</body>
</html>





