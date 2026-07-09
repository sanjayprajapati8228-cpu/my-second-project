<?php
@include 'config.php';

if (isset($_POST['Register'])) {
    $doctorsp = $_POST['doctorspeci'];
    $name = $_POST['name1'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Check if doctor already exists
    $select = "SELECT * FROM add_doctor WHERE Email = '$email'";
    $result = mysqli_query($conn, $select);

    if (mysqli_num_rows($result) > 0) {
        echo "<script>alert('Doctor already exists')</script>";
    } else {
        if ($password != $cpassword) {
            echo "<script>alert('Password and Confirm Password do not match')</script>";
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into database
            $query = mysqli_query($conn, "INSERT INTO add_doctor 
                (DoctorSpecialization, DoctorName, DoctorContectNO, Email, Password, Cpassword) 
                VALUES ('$doctorsp','$name','$phone','$email','$password','$cpassword')");

            if ($query) {
                // --- START EMAIL FUNCTION ---
                $to = $email;
                $subject = "Welcome to the Medical Portal - Account Created";
                
                $message = "
                <html>
                <head><meta charset='UTF-8'><title>Welcome Dr. $name</title>`r`n

    </head>
<body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
                    <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'>
                        <tr><td align='center'>
                            <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
                                <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Doctor Portal Access</td></tr>
                                <tr><td style='padding:24px;'>
                                    <h2 style='margin:0 0 10px;'>Hello Dr. $name,</h2>
                                    <p style='margin:0 0 14px;line-height:1.7;'>Your HMS account has been created by the administrator.</p>
                                    <table width='100%' cellpadding='0' cellspacing='0' style='background:#f8fbfe;border:1px solid #e2edf5;border-radius:10px;padding:14px;'>
                                        <tr><td style='padding:6px 0;'><strong>Email:</strong> $email</td></tr>
                                        <tr><td style='padding:6px 0;'><strong>Temporary Password:</strong> $password</td></tr>
                                    </table>
                                    <p style='margin:16px 0 0;line-height:1.7;'>Please log in and update your password after first sign in.</p>
                                </td></tr>
                                <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Admin Team</td></tr>
                            </table>
                        </td></tr>
                    </table>
</body>
                </html>";

                // Set content-type for sending HTML email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: HMS Admin <gujaratijeel15@gmail.com>' . "\r\n";
                $headers .= "Reply-To: gujaratijeel15@gmail.com\r\n";

                // Send the email
                mail($to, $subject, $message, $headers);
                // --- END EMAIL FUNCTION ---

                echo "<script>alert('Doctor added successfully and email sent!'); window.location.href='AdminDashboard.php';</script>";
            } else {
                echo "<script>alert('Data not inserted')</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Doctor</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="hms-ui-consistency.css" />
	<link rel="stylesheet" href="hms-theme.css" />
	<link rel="stylesheet" href="css/buttons.css" />
	<link rel='stylesheet' href='admin-sidebar.css'>
	<style>
		:root {
			--text-dark: #12212e;
			--text-muted: #557085;
			--shadow: 0 10px 28px rgba(7, 42, 67, 0.1);
		}

		body {
			margin: 0;
			font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
			color: var(--text-dark);
			background:
				radial-gradient(circle at 15% 5%, #d8f3ff 0%, transparent 34%),
				radial-gradient(circle at 92% 92%, #dff0ff 0%, transparent 28%),
				#eef4f8;
		}

		.main-content {
			padding: 28px;
		}

		.hero {
			background: linear-gradient(125deg, #ffffff, #eef7ff);
			border-radius: 20px;
			padding: 22px;
			box-shadow: var(--shadow);
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 16px;
			margin-bottom: 18px;
		}

		.hero h1 {
			margin: 0;
			font-size: 1.65rem;
			color: #0b7fab;
			font-weight: 700;
		}

		.hero p {
			margin: 8px 0 0;
			color: var(--text-muted);
		}

		.form-panel {
			background: #fff;
			border-radius: 16px;
			border: 1px solid #e2edf5;
			padding: 22px;
			box-shadow: var(--shadow);
			max-width: 760px;
			width: 100%;
			margin: 0 auto;
		}

		.form-panel h3 {
			font-size: 1.08rem;
			margin: 0 0 14px;
		}

		.form-label {
			font-weight: 600;
			color: #1c3f58;
		}

		.form-control {
			border-radius: 10px;
			border: 1px solid #d2e1ee;
		}

		.form-control:focus {
			border-color: #8ab7d6;
			box-shadow: 0 0 0 .2rem rgba(11, 127, 171, 0.14);
		}

		.form-grid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 14px;
		}

		.form-grid .full {
			grid-column: 1 / -1;
		}

		.actions {
			display: flex;
			gap: 12px;
			align-items: center;
			justify-content: space-between;
			margin-top: 10px;
		}

		@media (max-width: 768px) {
			.main-content {
				padding: 16px;
			}
			.hero {
				flex-direction: column;
				align-items: flex-start;
			}
			.form-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
</head>
<body>
	<?php $admin_active = 'add_doctor'; include 'admin_sidebar.php'; ?>

	<div class="main-content">
		<section class="hero">
			<div>
				<h1><i class="fas fa-user-md"></i> Add Doctor</h1>
				<p>Create doctor account with contact details and secure login credentials.</p>
			</div>
			<a href="FeatchDoctors.php" class="btn btn-outline-secondary hms-btn btn-secondary-action">
				<i class="fas fa-list"></i> View Doctors
			</a>
		</section>

		<section class="form-panel">
			<h3>Doctor Information</h3>
			<form method="POST">
				<div class="form-grid">
					<div>
						<label class="form-label">Doctor Specialization</label>
						<input class="form-control" name="doctorspeci" type="text" placeholder="Enter Doctor Specialization" required />
					</div>
					<div>
						<label class="form-label">Doctor Name</label>
						<input class="form-control" name="name1" type="text" placeholder="Enter Doctor Name" required />
					</div>
                    <div>
                        <label class="form-label">Doctor Contact No</label>
						<input class="form-control" type="tel" name="phone" placeholder="Enter Phone Number"
							pattern="[0-9]{10}" maxlength="10" required
							oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);" />
					</div>
					<div>
						<label class="form-label">Email</label>
						<input type="email" name="email" class="form-control" placeholder="Enter Email" required>
					</div>
					<div>
						<label class="form-label">Password</label>
						<input type="password" name="password" class="form-control" placeholder="Enter Password" required>
					</div>
					<div>
						<label class="form-label">Confirm Password</label>
						<input type="password" name="cpassword" class="form-control" placeholder="Confirm Password" required>
					</div>
				</div>

				<div class="actions">
					<a href="AdminDashboard.php" class="btn-navigation">&larr; Go Back</a>
					<button type="submit" name="Register" class="btn btn-primary-action">Register</button>
				</div>
			</form>
		</section>
	</div>

	<script src='admin-sidebar.js'></script>
</body>
</html>









