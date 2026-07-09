<?php
include 'config.php';

$doctor_id = $_GET['doctor_id'];
$date = $_GET['date'];
$time = $_GET['time'];

// Convert requested time to a 7-minute window
$start_time = $time;
$end_time = date("H:i:s", strtotime($time . " +7 minutes"));

// Check for any appointment that starts or ends within this 7-minute window
$query = "SELECT * FROM user_appointments 
          WHERE doctor_id = ? 
          AND appointment_Date = ? 
          AND (
              (appointment_Time <= ? AND DATE_ADD(appointment_Time, INTERVAL 7 MINUTE) > ?)
          )";

$stmt = $conn->prepare($query);
$stmt->bind_param("isss", $doctor_id, $date, $start_time, $start_time);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['available' => false]);
} else {
    echo json_encode(['available' => true]);
}
?>