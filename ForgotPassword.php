<?php
include 'config.php';
session_start();

// Reset the process if requested
if(isset($_GET['reset'])) {
    unset($_SESSION['otp_code']);
    unset($_SESSION['temp_data']);
    header("Location: ForgotPassword.php");
    exit();
}

// STEP 1: Handle Initial Request (Send OTP)
if(isset($_POST['update_password'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['new_pass']; // Don't escape yet if hashing
    $confirm_pass = $_POST['confirm_pass'];

    if($new_pass != $confirm_pass){
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        $check_email = mysqli_query($conn, "SELECT * FROM `user_registration1` WHERE Email = '$email'");
        
        if(mysqli_num_rows($check_email) > 0){
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);
            
            // Store all data in an array inside the session
            $_SESSION['temp_data'] = [
                'email' => $email,
                'password' => $new_pass,
                'otp' => $otp
            ];

            // --- EMAIL SENDING LOGIC ---
            $to = $email;
            $subject = "Your Password Reset OTP";
            $message = "
            <html><head><meta charset='UTF-8'><title>Password Reset OTP</title></head>
            <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
            <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
            <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Password Reset OTP</td></tr>
            <tr><td style='padding:24px;line-height:1.7;'><p>Your OTP code is:</p><p style='font-size:28px;font-weight:700;letter-spacing:3px;background:#f8fbfe;border:1px solid #e2edf5;border-radius:10px;display:inline-block;padding:8px 14px;'>$otp</p><p>This OTP is valid only for this reset request.</p></td></tr>
            <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Security Team</td></tr>
            </table></td></tr></table></body></html>";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: HMS Security <gujaratijeel15@gmail.com>\r\n";
            $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";

            if(mail($to, $subject, $message, $headers)){
                echo "<script>alert('OTP has been sent to your email.');</script>";
            } else {
                // If mail() fails, we still show the OTP field for testing/demo
                echo "<script>alert('OTP simulated (Mail server not configured). Your OTP is: $otp');</script>";
            }
        } else {
            echo "<script>alert('Email not found in our records');</script>";
        }
    }
}

// STEP 2: Handle OTP Verification
if(isset($_POST['verify_otp'])){
    $entered_otp = $_POST['otp_input'];

    // Check if the session exists before comparing (Fixes your error)
    if(isset($_SESSION['temp_data']['otp'])){
        if($entered_otp == $_SESSION['temp_data']['otp']){
            
            $email = $_SESSION['temp_data']['email'];
            $pass = mysqli_real_escape_string($conn, $_SESSION['temp_data']['password']);

            // Update Database
            $update = mysqli_query($conn, "UPDATE `user_registration1` SET Password = '$pass' WHERE Email = '$email'");

            if($update){
                // Send final confirmation email
                $updated_subject = "Password Updated";
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8\r\n";
                $headers .= "From: HMS Security <gujaratijeel15@gmail.com>\r\n";
                $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";
                $updated_message = "
                <html><head><meta charset='UTF-8'><title>Password Updated</title></head>
                <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
                <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
                <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                <tr><td style='background:linear-gradient(90deg,#1d6b3f,#2d9f5a);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Password Updated</td></tr>
                <tr><td style='padding:24px;line-height:1.7;'><p>Your password has been updated successfully.</p><p>You can now log in with your new password.</p></td></tr>
                <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Security Team</td></tr>
                </table></td></tr></table></body></html>";
                mail($email, $updated_subject, $updated_message, $headers);
                
                // Clear session data
                unset($_SESSION['temp_data']);
                echo "<script>alert('Password updated successfully!'); window.location.href='Logins.html';</script>";
            } else {
                echo "<script>alert('Could not update password. Please try again.');</script>";
            }
        } else {
            echo "<script>alert('Invalid OTP. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Session expired. Please request a new OTP.'); window.location.href='ForgotPassword.php?reset=1';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Update Password</title>
    <style>
        body { background: #f0f2f5; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', serif; }
        .form-container { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); width: 100%; max-width: 450px; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>

<div class="form-container">
    <?php if(!isset($_SESSION['temp_data'])): ?>
        <form action="" method="post">
            <h3 class="text-center mb-4">Forgot Password</h3>
            <div class="mb-3">
                <label class="form-label">Registered Email</label>
                <input type="email" name="email" class="form-control" required placeholder="Enter your email">
            </div>
            <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" name="new_pass" class="form-control" required placeholder="Create new password">
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="confirm_pass" class="form-control" required placeholder="Confirm new password">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="update_password" class="btn btn-secondary-action">Send OTP</button>
                <a href="UserLogin.php" class="text-center mt-2 text-decoration-none">&larr; Back to Login</a>
            </div>
        </form>
    <?php else: ?>
        <form action="" method="post">
            <h3 class="text-center mb-4">Verify OTP</h3>
            <p class="text-center text-muted">Enter the code sent to<br><b><?php echo $_SESSION['temp_data']['email']; ?></b></p>
            <div class="mb-3">
                <input type="number" min="0" name="otp_input" class="form-control text-center fs-4" placeholder="000000" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" name="verify_otp" class="btn btn-success btn-secondary-action">Update Password</button>
                <a href="ForgotPassword.php?reset=1" class="text-center mt-2 text-danger text-decoration-none btn-danger-action">Cancel & Restart</a>
            </div>
        </form>
    <?php endif; ?>
</div>

</body>
</html>





