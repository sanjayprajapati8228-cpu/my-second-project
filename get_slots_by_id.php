<?php
include 'config.php';

if (isset($_GET['doctor_id'])) {
    $doctor_id = intval($_GET['doctor_id']);
    
    // Selects slots specifically for the chosen doctor
    $query = "SELECT available_day, start_time, end_time FROM doctor_schedule WHERE doctor_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $slots = [];
    while ($row = $result->fetch_assoc()) {
        // Formats time into readable AM/PM format
        $time_range = date("g:i A", strtotime($row['start_time'])) . " - " . date("g:i A", strtotime($row['end_time']));
        $slots[] = [
            'day' => $row['available_day'],
            'time' => $time_range
        ];
    }
    header('Content-Type: application/json');
    echo json_encode($slots);
}
?>