<?php
	@include 'config.php';
		
	$msg="";
	if(isset($_POST['Register'])){
			
		$fname = $_POST['fname'];
		$gender = $_POST['gender'];
		$phone = $_POST['phone'];
		$email = $_POST['email'];
		$address = $_POST['address'];
		$password = $_POST['password'];
		$cpassword =$_POST['cpassword'];
		
		// ... (Existing code for email check and password match)
		
		$select = "SELECT * FROM user_registration1 WHERE Email = '$email'";
        $result = mysqli_query($conn, $select);

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('User already exists'); window.location.href='User_Registration.php';</script>";
        } else {
            if ($password != $cpassword) {
                echo "<script>alert('Password and Confirm Password do not match')</script>";
            } else {
                // Insert only one password (not cpassword)
                $query = mysqli_query($conn, "INSERT INTO user_registration1 (Fname, Gender, Phone, Email, Address, Password, CPassword) 
                VALUES ('$fname','$gender','$phone','$email','$address','$password','$cpassword')");
                
                if ($query) {
                    // *** ADD EMAIL SENDING LOGIC HERE ***
                    $to = $email; // The user's email address
                    $subject = "Welcome to Our Hospital Registration!";
                    $message = "
                        <html>
                        <head><meta charset='UTF-8'><title>Registration Confirmation</title></head>
                        <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
                            <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'>
                                <tr>
                                    <td align='center'>
                                        <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                                            <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>HMS Registration</td></tr>
                                            <tr><td style='padding:24px;'>
                                                <h2 style='margin:0 0 10px;font-size:22px;'>Dear $fname,</h2>
                                                <p style='margin:0 0 14px;line-height:1.7;'>Thank you for registering with HMS. Your account has been created successfully.</p>
                                                <table width='100%' cellpadding='0' cellspacing='0' style='background:#f8fbfe;border:1px solid #e2edf5;border-radius:10px;padding:14px;'>
                                                    <tr><td style='padding:6px 0;'><strong>Full Name:</strong> $fname</td></tr>
                                                    <tr><td style='padding:6px 0;'><strong>Gender:</strong> $gender</td></tr>
                                                    <tr><td style='padding:6px 0;'><strong>Phone:</strong> $phone</td></tr>
                                                    <tr><td style='padding:6px 0;'><strong>Email:</strong> $email</td></tr>
                                                    <tr><td style='padding:6px 0;'><strong>Address:</strong> $address</td></tr>
                                                </table>
                                                <p style='margin:16px 0 0;line-height:1.7;'>You can now log in and book appointments from your dashboard.</p>
                                            </td></tr>
                                            <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Automanager Team</td></tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </body>
                        </html>";
                    
                    // Always set content-type when sending HTML email
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    
                    // More headers (e.g., sending from a specific address)
                    // **NOTE:** Replace 'no-reply@yourdomain.com' with a valid email from your server.
                    $headers .= 'From: HMS <gujaratijeel15@gmail.com>' . "\r\n";
                    $headers .= 'Reply-To: gujaratijeel15@gmail.com' . "\r\n";
                    
                    // Send the email
                    if(mail($to, $subject, $message, $headers)) {
                         // Email sent successfully
                    } else {
                         // Failed to send email (optional: log this error)
                    }

                    // *** END EMAIL SENDING LOGIC ***
                    
                    echo "<script>alert('Registration Successful. A confirmation email has been sent.'); window.location.href='UserLogin.php';</script>";
                } else {
                    echo "<script>alert('Data not inserted'); window.location.href='User_Registration.php';</script>";
                }
            }
        }
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Hospital User Registration</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Bootstrap CSS CDN -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

	<style>
		body {
			background: linear-gradient(135deg, #e6f7ff, #f0f9f9);
			min-height: 100vh;
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 15px;
			font-family: Georgia, serif;
		}

		.form-container {
			background: #fff;
			padding: 30px;
			border-radius: 12px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
			width: 100%;
			max-width: 500px;
		}

		.form-title {
			text-align: center;
			margin-bottom: 25px;
			color: #007bff;
			font-weight: bold;
		}

		/* Mobile adjustments */
		@media (max-width: 576px) {
			.form-container {
				padding: 20px;
			}
			.form-title {
				font-size: 1.4rem;
			}
			label {
				font-size: 0.9rem;
			}
			input, select, button {
				font-size: 0.9rem !important;
			}
		}
	</style>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="hms-ui-consistency.css" />
	<link rel="stylesheet" href="hms-theme.css" />
	<link rel="stylesheet" href="css/buttons.css" />
</head>
<body>

<div class="form-container">
    <h2 class="form-title">User Registration</h2>
    <form id="registrationForm" method="POST">
		<div class="mb-3">
			<label for="fullname" class="form-label">Full Name</label>
			<input type="text" name="fname" class="form-control" id="fullname" placeholder="Enter Your Full Name  " required>
		</div>
		
		<div class="mb-3">
			<label for="gender" class="form-label">Gender</label>
			<select class="form-select" name="gender" id="gender"  required>
			  <option value="">-- Select --</option>
			  <option>Male</option>
			  <option>Female</option>
			  <option>Other</option>
			</select>
		</div>

		<div class="mb-3">
			<label for="phone" class="form-label">Phone Number</label>
			<input name="phone" class="form-control" type="tel" id="number"
				pattern="[0-9]{10}" maxlength="10" placeholder="Enter Your Phone Number " required
				oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);" />
		</div>

		<div class="mb-3">
			<label for="email" class="form-label">Email Address</label>
			<input type="email" name="email" class="form-control" id="email" placeholder="Enter Your Email " required>
		</div>

		<div class="mb-3">
			<label for="address" class="form-label">Address</label>
			<input type="text" name="address" class="form-control" id="address" placeholder="Enter Your Address " required>
		</div>

		<div class="mb-3">
			<label for="password" class="form-label">Password</label>
			<input type="password" name="password" id="password" class="form-control" placeholder="Enter  Password " required>
		</div>

		<div class="mb-3">
			<label for="confirmPassword" class="form-label">Confirm Password</label>
			<input type="password" name="cpassword" id="confirmPassword" class="form-control" placeholder="Enter  Confirm Password " required>
		</div>

		<div id="error" class="text-danger mb-3" style="display: none;"></div>

		<div class="d-flex justify-content-between align-items-center mb-3">
			<a href="UserLogin.php" style="text-decoration: none; color:#0056b3">Already have an account?</a>
		</div>

		<div class="d-grid">
			<button type="submit" name="Register" class="btn btn-primary-action">Register</button>
		</div>
    </form>
</div>

<!-- Bootstrap JS CDN (with Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>






