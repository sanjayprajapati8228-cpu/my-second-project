<?php
@include('config.php');
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: UserLogin.php");
    exit();
}?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Appointment Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #e6f7ff, #f0f9f9); font-family: Georgia, serif; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .form-container { background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 550px; }
        .form-title { text-align: center; margin-bottom: 25px; color: #007bff; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
<div class="form-container">
    <h2 class="form-title">Appointment Form</h2>
    <form id="appointmentForm" action="payment.php" method="POST" enctype="multipart/form-data">
        <h3>Contact Information</h3>
        <hr>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input class="form-control" type="text" name="name" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input class="form-control" type="tel" name="phone" pattern="[0-9]{10}" maxlength="10" required>
        </div>

        <h3>Appointment Information</h3>
        <hr>
        <div class="mb-3">
            <label class="form-label">Doctor Name</label>
            <select class="form-select" name="doctorname" id="doctorid" required onchange="fetchDoctorSlots(this.value)">
                <option value="" disabled selected>-- Select Doctor --</option>
                <?php
                    // Fetch doctors from your add_doctor table
                    $results = $conn->query("SELECT id, DoctorName, DoctorSpecialization FROM add_doctor");
                    while($row = $results->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['DoctorName']; ?> (<?php echo $row['DoctorSpecialization']; ?>)</option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Available Slots</label>
            <select name="selected_slot" id="slot_select" class="form-select" required>
                <option value="">Select a doctor first</option>
            </select>
            <div class="form-text">Choose from the doctor's scheduled availability.</div>
        </div>
        
        <input type="hidden" name="date" id="app_date">
        <input type="hidden" name="time" id="app_time">

        <div class="mb-3">
            <label class="form-label">Patient Type</label>
            <select class="form-select" name="patient_type" required>
                <option value="" disabled selected>-- Select Type --</option>
                <option value="New">New</option>
                <option value="Old">Old</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="form-label">Upload Previous Report (Optional)</label>
            <input type="file" name="patient_report" class="form-control" accept=".pdf, image/*">
            <div class="form-text">Accepted formats: PDF, JPG, PNG</div>
        </div>

        <div class="form-text mb-3">Appointment updates are sent to your registered Gmail.</div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary-action">Proceed to Payment</button>
        </div>
    </form>
</div>

<script>
function fetchDoctorSlots(doctorId) {
    const slotSelect = document.getElementById('slot_select');
    if (!doctorId) {
        slotSelect.innerHTML = '<option value="">Select a doctor first</option>';
        return;
    }

    // Fetches slots based on the doctor_id from doctor_schedule table
    fetch('get_slots_by_id.php?doctor_id=' + doctorId)
        .then(response => response.json())
        .then(data => {
            slotSelect.innerHTML = '<option value="" disabled selected>-- Choose Available Slot --</option>';
            if(data.length === 0) {
                slotSelect.innerHTML = '<option value="">No slots scheduled for this doctor</option>';
            } else {
                data.forEach(slot => {
                    let option = document.createElement('option');
                    // Format used for display and internal splitting: "Monday | 09:00 AM - 10:00 AM"
                    let displayText = slot.day + " | " + slot.time;
                    option.value = displayText;
                    option.textContent = displayText;
                    slotSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error fetching slots:', error);
            slotSelect.innerHTML = '<option value="">Error loading slots</option>';
        });
}

// Splits the selected slot into separate Date and Time values for the backend
document.getElementById('slot_select').onchange = function() {
    const selectedText = this.value;
    if (selectedText.includes(" | ")) {
        const parts = selectedText.split(' | ');
        document.getElementById('app_date').value = parts[0]; // Monday, Tuesday, etc.
        document.getElementById('app_time').value = parts[1]; // 09:00 AM - 10:00 AM
    }
};

document.getElementById('appointmentForm').onsubmit = function(e) {
    // Slot Selection Validation
    const slot = document.getElementById('slot_select').value;
    if (!slot || slot.includes("Select a doctor") || slot.includes("No slots scheduled")) {
        e.preventDefault();
        alert("Please select a valid available appointment slot.");
        return false;
    }

    return true;
};
</script>
        <div class="form-text mb-3">Appointment updates are sent to your registered Gmail.</div>

</body>
</html>





