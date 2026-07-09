<?php
@include('config.php');
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['appointment_data'])) {
    header("Location: User_appointment.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$data = $_SESSION['appointment_data'];

// Extract info
$name = $data['name'];
$email = '';
$phone = $data['phone'];
$doctor_id = $data['doctorname'];
$app_date = $data['appointment_date'];
$app_time = $data['formatted_time'];
$patient_type = $data['patient_type'];
$report_filename = $data['report_name'];

// Pricing logic
$amount = ($patient_type == 'New') ? 500 : 300;

// Always use the logged-in user's registered email (not session/form input).
$user_stmt = $conn->prepare("SELECT Email FROM user_registration1 WHERE id = ? LIMIT 1");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_row = $user_stmt->get_result()->fetch_assoc();
$email = $user_row['Email'] ?? '';
if ($email === '') {
    unset($_SESSION['appointment_data']);
    echo "<script>alert('Your profile email was not found. Please login again.'); window.location.href='UserLogin.php';</script>";
    exit();
}

if (!preg_match('/^[a-zA-Z0-9._%+-]+@gmail\\.com$/', $email)) {
    unset($_SESSION['appointment_data']);
    echo "<script>alert('Please register with a Gmail address to book an appointment.'); window.location.href='User_Dahboard.php';</script>";
    exit();
}

// Fetch doctor details for email
$doc_query = $conn->prepare("SELECT DoctorName, Email FROM add_doctor WHERE id = ?");
$doc_query->bind_param("i", $doctor_id);
$doc_query->execute();
$doctor_data = $doc_query->get_result()->fetch_assoc();
if (!$doctor_data) {
    unset($_SESSION['appointment_data']);
    echo "<script>alert('Selected doctor is no longer available. Please book again.'); window.location.href='User_appointment.php';</script>";
    exit();
}
$doctor_name = $doctor_data['DoctorName'];
$doctor_email = $doctor_data['Email'];

if (isset($_POST['confirm_payment'])) {
    // Re-check slot availability at final submission to avoid double booking
    $busy_check = $conn->prepare(
        "SELECT id FROM user_appointments
         WHERE doctor_id = ?
           AND appointment_Date = ?
           AND (appointment_Time <= ? AND DATE_ADD(appointment_Time, INTERVAL 7 MINUTE) > ?)
         LIMIT 1"
    );
    $busy_check->bind_param("isss", $doctor_id, $app_date, $app_time, $app_time);
    $busy_check->execute();
    $busy_result = $busy_check->get_result();
    if ($busy_result && $busy_result->num_rows > 0) {
        echo "<script>alert('This time slot was just booked by another patient. Please choose another slot.'); window.location.href='User_appointment.php';</script>";
        exit();
    }

    // 1. Insert into database with 'Pending' status
    $stmt = $conn->prepare("INSERT INTO user_appointments (patient_id, Name, Email, Phone, doctor_id, doctor_name, appointment_Date, appointment_Time, patient_type, patient_report, cash, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("isssisssssi", $user_id, $name, $email, $phone, $doctor_id, $doctor_name, $app_date, $app_time, $patient_type, $report_filename, $amount);
	
	

    if ($stmt->execute()) {
        // 2. Send Emails
                $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: HMS Appointments <gujaratijeel15@gmail.com>\r\n";
        $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
        $email_content = "
        <html>
        <head><meta charset='UTF-8'><title>Appointment Request</title></head>
        <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'>
                <tr><td align='center'>
                    <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                        <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Appointment Request Received</td></tr>
                        <tr><td style='padding:24px;'>
                            <p style='margin:0 0 14px;line-height:1.7;'>Your booking request has been submitted successfully.</p>
                            <table width='100%' cellpadding='0' cellspacing='0' style='background:#f8fbfe;border:1px solid #e2edf5;border-radius:10px;padding:14px;'>
                                <tr><td style='padding:6px 0;'><strong>Patient:</strong> $name</td></tr>
                                <tr><td style='padding:6px 0;'><strong>Doctor:</strong> Dr. $doctor_name</td></tr>
                                <tr><td style='padding:6px 0;'><strong>Date:</strong> $app_date</td></tr>
                                <tr><td style='padding:6px 0;'><strong>Time:</strong> " . date("h:i A", strtotime($app_time)) . "</td></tr>
                                <tr><td style='padding:6px 0;'><strong>Paid:</strong> Rs. $amount</td></tr>
                                <tr><td style='padding:6px 0;'><strong>Status:</strong> Pending (Awaiting Doctor Confirmation)</td></tr>
                            </table>
                        </td></tr>
                        <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Appointments Team</td></tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>";

        @mail($email, "Appointment Request Pending", $email_content, $headers); // To Patient
        @mail($doctor_email, "New Booking Request: $name", $email_content, $headers); // To Doctor

        // 3. Cleanup and Redirect
        unset($_SESSION['appointment_data']);
        echo "<script>alert('Booking Requested! Status is currently Pending. Emails sent to your registered Gmail and Dr. $doctor_name.'); window.location.href='User_Dahboard.php';</script>";
        exit();
    } else {
        die("Error processing booking: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Payment Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle at 12% 0%, #d9edff 0%, transparent 34%), #edf4fa;
            padding: 16px 0;
            font-family: "Segoe UI", sans-serif;
        }
        .page-wrap { max-width: 760px; margin: 0 auto; }
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
        .topbar h5 { margin: 0; color: #0d3d5a; }
        .topbar a { text-decoration: none; color: #2459d2; font-weight: 600; }
        .payment-card {
            max-width: 760px;
            margin: auto;
            background: white;
            padding: 24px;
            border-radius: 14px;
            border: 1px solid #dbe7f1;
            box-shadow: 0 12px 30px rgba(8,46,72,0.08);
        }
        .price-badge { font-size: 1.5rem; color: #1f8a5b; font-weight: bold; }
        .payment-option { border: 1px solid #d6e4ef; padding: 12px; border-radius: 8px; cursor: pointer; margin-bottom: 10px; display: block; background: #f9fcff; }
        #qr_section { display: none; margin-top: 15px; text-align: center; border: 1px dashed #9cb7d3; padding: 15px; border-radius: 8px; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
<div class="container text-center">
    <div class="page-wrap">
    <div class="topbar">
        <h5>HMS Payment Portal</h5>
        <a href="User_Dahboard.php" class="btn-navigation">Back to Dashboard</a>
    </div>
    <div class="payment-card text-start">
        <h3 class="text-center mb-4">Payment Summary</h3>
        <div class="card mb-4 border-0 bg-light">
            <div class="card-body">
                <p><b>Patient:</b> <?php echo $name; ?></p>
                <p><b>Doctor:</b> Dr. <?php echo $doctor_name; ?></p>
                <p><b>Date:</b> <?php echo $app_date; ?></p>
                <p><b>Time:</b> <?php echo date("h:i A", strtotime($app_time)); ?></p>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="h5">Total Amount:</span>
                    <span class="price-badge">&#8377;<?php echo $amount; ?></span>
                </div>
            </div>
        </div>

        <form method="POST">
            <label class="form-label fw-bold">Select Mode:</label>
            <label class="payment-option"><input type="radio" name="pm" value="UPI" onclick="showQR()" required> UPI / QR Code</label>
            <label class="payment-option"><input type="radio" name="pm" value="Card" onclick="hideQR()"> Credit / Debit Card</label>

            <div id="qr_section">
                <p>Scan to pay <strong>&#8377;<?php echo $amount; ?></strong></p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=upi://pay?pa=hospital@upi&am=<?php echo $amount; ?>">
            </div>

            <button type="submit" name="confirm_payment" class="btn btn-success w-100 mt-4 btn-lg btn-primary-action">Complete Booking</button>
        </form>
    </div>
    </div>
</div>
<script>
    function showQR() { document.getElementById('qr_section').style.display = 'block'; }
    function hideQR() { document.getElementById('qr_section').style.display = 'none'; }
</script>
</body>
</html>






