<?php
@include('config.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: UserLogin.php");
    exit();
}

$form = [];
$flash_error = '';
if (isset($_SESSION['appointment_form']) && is_array($_SESSION['appointment_form'])) {
    $form = $_SESSION['appointment_form'];
    unset($_SESSION['appointment_form']);
}
if (isset($_SESSION['appointment_error']) && is_string($_SESSION['appointment_error'])) {
    $flash_error = $_SESSION['appointment_error'];
    unset($_SESSION['appointment_error']);
}

function hms_redirect_back_to_form($message)
{
    $_SESSION['appointment_form'] = $_POST;
    $_SESSION['appointment_error'] = $message;
    header("Location: User_appointment.php");
    exit();
}

// --- PHP VALIDATION LOGIC ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['proceed_payment'])) {
    // Always use the logged-in user's registered email (not form input).
    $user_id = $_SESSION['user_id'];
    $user_stmt = $conn->prepare("SELECT Email FROM user_registration1 WHERE id = ? LIMIT 1");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_row = $user_stmt->get_result()->fetch_assoc();
    $userEmail = $user_row['Email'] ?? '';
    if ($userEmail === '') {
        echo "<script>alert('Your profile email was not found. Please login again.'); window.location.href='UserLogin.php';</script>";
        exit();
    }

    // If you want Gmail-only enforcement, keep this check.
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $userEmail)) {
        echo "<script>alert('Please register with a Gmail address to book an appointment.'); window.location.href='User_Dahboard.php';</script>";
        exit();
    }

    $doctor_id = $_POST['doctorname'];
    $app_date = $_POST['appointment_date'];
    $selected_time_str = $_POST['appointment_time']; 
    $slot_text = $_POST['selected_slot']; 

    // 1. Time Range Validation
    $parts = explode(' | ', $slot_text);
    $time_range = explode(' - ', $parts[1]);
    
    $selected_time = strtotime($selected_time_str);
    $slot_start = strtotime($time_range[0]);
    $slot_end = strtotime($time_range[1]);

    if ($selected_time < $slot_start || $selected_time > $slot_end) {
        hms_redirect_back_to_form("Error: Your selected time is outside the doctor's slot ($parts[1]).");
    }

    // 2. 7-Minute Block Check
    $formatted_selected_time = date("H:i:s", $selected_time);
    
    // Select the conflicting appointment time to calculate the next available gap
    $check_query = "SELECT appointment_Time FROM user_appointments 
                    WHERE doctor_id = ? 
                    AND appointment_Date = ? 
                    AND (
                        (appointment_Time <= ? AND DATE_ADD(appointment_Time, INTERVAL 7 MINUTE) > ?)
                    ) LIMIT 1";
    
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("isss", $doctor_id, $app_date, $formatted_selected_time, $formatted_selected_time);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $existing_time = $row['appointment_Time'];
        
        // Logic: existing time + 7 minutes
        $next_available = date("H:i", strtotime($existing_time . " +7 minutes"));
        
        hms_redirect_back_to_form("This 7-minute window is busy. You can book an appointment after $next_available o'clock.");
    }

    // 3. Temporary File Handling
    $report_name = NULL;
    if (!empty($_FILES['patient_report']['name'])) {
        $report_name = time() . '_' . $_FILES['patient_report']['name'];
        move_uploaded_file($_FILES['patient_report']['tmp_name'], "uploads/" . $report_name);
    }

    // 4. Store all data in SESSION and redirect to payment.php
    $_SESSION['appointment_data'] = $_POST;
    $_SESSION['appointment_data']['email'] = $userEmail;
    $_SESSION['appointment_data']['formatted_time'] = $formatted_selected_time;
    $_SESSION['appointment_data']['report_name'] = $report_name;
    
    header("Location: payment.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at 15% 0%, #d8ecff 0%, transparent 35%), #eef4f9;
            font-family: "Segoe UI", Tahoma, sans-serif;
            min-height: 100vh;
            padding: 16px;
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
        .topbar h4 { margin: 0; font-size: 1.02rem; color: #0d3d5a; }
        .topbar a { text-decoration: none; color: #2459d2; font-weight: 600; }
        .form-container {
            background: #fff;
            padding: 24px;
            border: 1px solid #dbe7f1;
            border-radius: 14px;
            box-shadow: 0 12px 30px rgba(8, 46, 72, 0.08);
        }
        .form-title { text-align: center; margin-bottom: 18px; color: #0d3d5a; font-weight: 700; }
        h5 { color: #2c4b62; margin-bottom: 10px; }
        .form-control, .form-select { border-radius: 8px; border-color: #d4e2ee; }
        .form-control:focus, .form-select:focus { border-color: #7fa9ef; box-shadow: 0 0 0 .2rem rgba(36, 89, 210, .14); }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
<div class="page-wrap">
    <div class="topbar">
        <h4>HMS Appointment Booking</h4>
        <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
    </div>
<div class="form-container">
    <h2 class="form-title">Appointment Form</h2>
    <form method="POST" enctype="multipart/form-data">
        <h5>Contact Details</h5>
        <div class="mb-3"><input class="form-control" type="text" name="name" value="<?php echo isset($form['name']) ? htmlspecialchars($form['name']) : ''; ?>" placeholder="Full Name" required></div>
        <div class="mb-3"><input class="form-control" type="tel" name="phone" value="<?php echo isset($form['phone']) ? htmlspecialchars($form['phone']) : ''; ?>" placeholder="Phone" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required></div>

        <hr>
        <h5>Appointment Details</h5>
        <div class="mb-3">
            <label>Select Doctor</label>
            <select class="form-select" name="doctorname" id="doctorid" required onchange="fetchDoctorSlots(this.value)">
                <option value="" disabled selected>-- Choose Doctor --</option>
                <?php
                    $results = $conn->query("SELECT id, DoctorName, DoctorSpecialization FROM add_doctor");
                    while($row = $results->fetch_assoc()) {
                        $selected = (isset($form['doctorname']) && $form['doctorname'] == $row['id']) ? 'selected' : '';
                        echo "<option value='{$row['id']}' $selected>{$row['DoctorName']} ({$row['DoctorSpecialization']})</option>";
                    }
                ?>
            </select>
        </div>
        <div class="mb-3"><label>Date</label><input type="date" name="appointment_date" id="appointment_date" class="form-control" value="<?php echo isset($form['appointment_date']) ? $form['appointment_date'] : ''; ?>" required min="<?php echo date('Y-m-d'); ?>"></div>
        
        <div class="mb-3">
            <label>Available Slots</label>
            <select name="selected_slot" id="slot_select" class="form-select" required>
                <option value="">Select Doctor First</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Select Time</label>
            <input type="time" name="appointment_time" id="appointment_time" class="form-control" value="<?php echo isset($form['appointment_time']) ? $form['appointment_time'] : ''; ?>" required>
            <div id="time-warning" class="form-text text-primary"></div>
        </div>

        <div class="mb-3">
            <select class="form-select" name="patient_type" required>
                <option value="" disabled selected>Patient Type</option>
                <option value="New" <?php echo (isset($form['patient_type']) && $form['patient_type'] == 'New') ? 'selected' : ''; ?>>New</option>
                <option value="Old" <?php echo (isset($form['patient_type']) && $form['patient_type'] == 'Old') ? 'selected' : ''; ?>>Old</option>
            </select>
        </div>
        <div class="mb-3"><label>Upload Report (Optional)</label><input type="file" name="patient_report" class="form-control" accept=".pdf, image/*"></div>
        <div class="form-text mb-3">Appointment updates are sent to your registered Gmail.</div>

        <button type="submit" name="proceed_payment" class="btn btn-primary-action">Proceed to Payment</button>
    </form>
</div>
</div>

<script>
function fetchDoctorSlots(doctorId) {
    const slotSelect = document.getElementById('slot_select');
    const oldSlot = <?php echo json_encode(isset($form['selected_slot']) ? $form['selected_slot'] : ''); ?>;

    if (!doctorId) return;

    fetch('get_slots_by_id.php?doctor_id=' + doctorId)
        .then(res => res.json())
        .then(data => {
            slotSelect.innerHTML = '<option value="" disabled selected>-- Select Slot --</option>';
            data.forEach(slot => {
                let opt = document.createElement('option');
                let slotVal = slot.day + " | " + slot.time;
                opt.value = slotVal;
                opt.textContent = slotVal;
                if(slotVal === oldSlot) { 
                    opt.selected = true; 
                }
                slotSelect.appendChild(opt);
            });

            if(slotSelect.value) {
                const parts = slotSelect.value.split(' | ');
                if(parts.length > 1) {
                    document.getElementById('time-warning').innerText = "Allowed: " + parts[1];
                }
            }
        })
        .catch(err => console.error("Error fetching slots:", err));
}

document.addEventListener("DOMContentLoaded", function() {
    const doctorId = document.getElementById('doctorid').value;
    if(doctorId) {
        fetchDoctorSlots(doctorId);
    }
});

document.getElementById('slot_select').onchange = function() {
    const parts = this.value.split(' | ');
    if(parts.length > 1) {
        document.getElementById('time-warning').innerText = "Allowed: " + parts[1];
    }
};
</script>
<?php if ($flash_error !== '') { ?>
<script>
    alert(<?php echo json_encode($flash_error); ?>);
</script>
<?php } ?>

</body>
</html>
