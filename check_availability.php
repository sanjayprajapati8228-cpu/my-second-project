<?php
@include('config.php');

if (isset($_POST['doctor_id']) && isset($_POST['date']) && isset($_POST['time'])) {
    $doctor_id = $_POST['doctor_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Check if any appointment exists for this doctor at this specific date and time
    $stmt = $conn->prepare("SELECT id FROM user_appointments WHERE doctor_id = ? AND appointment_Date = ? AND appointment_Time = ?");
    $stmt->bind_param("iss", $doctor_id, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "busy";
    } else {
        echo "free";
    }
    $stmt->close();
}
?>