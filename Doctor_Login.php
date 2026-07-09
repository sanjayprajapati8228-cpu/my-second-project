<?php
	@include 'config.php';
	session_start();
    
	if(isset($_POST['Login'])){
		
		// Use mysqli_real_escape_string to prevent basic SQL injection
		$email = mysqli_real_escape_string($conn, $_POST['email']);
		$password = mysqli_real_escape_string($conn, $_POST['password']);

		// Select query to verify doctor credentials
		$select = "SELECT * FROM add_doctor WHERE Email = '$email' AND Password = '$password'";

		$result = mysqli_query($conn, $select);

		if(mysqli_num_rows($result) > 0){
			$row = mysqli_fetch_assoc($result);
            
			// Establish the doctor_id session for dashboard and schedule access
			$_SESSION['doctor_id'] = $row['id']; 
            
			// Redirect to the doctor dashboard
			header("location:Doctor_dashboard.php");
            exit();
		}else{
			echo "<script>alert('This user does not exist');</script>";
		}
	}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Doctor Login Form</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">

	<style>
		body {
			background: linear-gradient(135deg, #e6f7ff, #f0f9f9);
			font-family: Georgia, serif;
			min-height: 100vh;
			display: flex;
			justify-content: center;
			align-items: center;
			padding: 20px;
		}

		.form-container {
			background: #fff;
			padding: 30px;
			border-radius: 12px;
			box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
			width: 100%;
			max-width: 550px;
		}
  </style>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="hms-ui-consistency.css" />
	<link rel="stylesheet" href="hms-theme.css" />
	<link rel="stylesheet" href="css/buttons.css" />
</head>

<body>
<div class="form-container">
    <form id="loginForm" method="POST">
		<div class="mb-3 text-center">
			<h1>Log in</h1>
		</div>

		<div class="mb-3">
			<label class="form-label" for="userId">User ID</label>
			<input class="form-control" type="text" id="userId" name="email" placeholder="Enter email ID" required>
		</div>

		<div class="mb-3">
			<label class="form-label" for="password">Password</label>
			<input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required>
		</div>

		<a href="Logins.html" style="text-decoration: none; color:#0056b3">&larr; Go Back</a>
			
		<div class="d-grid mb-3">
			<button class="btn btn-primary-action" name="Login" type="submit">Login</button>
		</div>
	</form>
 </div>

</body>
</html>




