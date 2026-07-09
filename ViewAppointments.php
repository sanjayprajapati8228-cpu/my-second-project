<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('location:UserLogin.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <style>
        body {
            font-family: "Segoe UI", Tahoma, sans-serif;
            margin: 0;
            padding: 16px;
            background: radial-gradient(circle at 12% 0%, #d9edff 0%, transparent 34%), #edf4fa;
            color: #173247;
        }
        .page-wrap { max-width: 860px; margin: 0 auto; }
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
        .topbar h4 { margin: 0; font-size: 1.03rem; color: #0d3d5a; }
        .topbar a { text-decoration: none; color: #2459d2; font-weight: 600; }
        .box {
            background: #fff;
            padding: 22px;
            border-radius: 14px;
            border: 1px solid #dbe7f1;
            box-shadow: 0 12px 30px rgba(8,46,72,0.08);
        }
        h2 { text-align: center; color: #0d3d5a; margin-bottom: 18px; }
        ul { list-style: none; padding: 0; margin: 0; }
        li {
            background: #f8fbff;
            border: 1px solid #d9e7f2;
            padding: 13px;
            margin-bottom: 10px;
            border-radius: 10px;
            font-size: 14px;
            line-height: 1.5;
        }
        strong { color: #0d3d5a; }
    </style>
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
<div class="page-wrap">
    <div class="topbar">
        <h4>HMS Appointments</h4>
        <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
    </div>
    <div class="box">
        <h2>My Appointments</h2>
        <?php
            $sql = "SELECT a.appointment_Date, a.appointment_Time, d.DoctorName AS doctor_name, d.DoctorSpecialization
                FROM user_appointments a
                JOIN add_doctor d ON a.doctor_id = d.id
                WHERE a.patient_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<ul>";
                while($row = $result->fetch_assoc()) {
                    echo "<li><strong>Date:</strong> " . $row['appointment_Date'] . "<br>";
                    echo "<strong>Time:</strong> " . $row['appointment_Time'] . "<br>";
                    echo "<strong>Doctor:</strong> " . $row['doctor_name'] . " (" . $row['DoctorSpecialization'] . ")</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>You have no appointments scheduled.</p>";
            }
            $stmt->close();
        ?>
    </div>
</div>
</body>
</html>
