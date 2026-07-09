<?php
	@include 'config.php';
	session_start();
	if(isset($_POST['Login'])){
		
		$email = $_POST['email'];
		$password = $_POST['password'];

		$select = " Select * from user_registration1 where Email  = '$email' && Password = '$password' ";

		$result = mysqli_query($conn, $select);

		if(mysqli_num_rows($result) > 0){
			$row = mysqli_fetch_assoc($result);
			$_SESSION['user_id'] = $row['id'];
			header("location:User_Dahboard.php");
		}else{
			echo "<script>alert('this user dosenot exixt ')</script>";
		}
	}
?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>User Login Form</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

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
		<form action="" method="POST">
			<div class="mb-3">
				<center>
				  <h1>Log in</h1>
				</center>
			</div>
			<div class="mb-3">
				<label class="form-label" for="userid">User Id</label><br>
				<input class="form-control" type="text" id="userId" name="email" placeholder="Enter Your Email Id " required>
			</div>

			<div class="mb-3">
				<label for="password" class="form-label">Password</label>
				<input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
			</div>

			<a href="Logins.html" style="text-decoration: none; color:#0056b3">&larr; Go Back</a>
			
				
				
			

			<div class="d-grid">
				<button type="submit" name="Login" class="btn btn-primary-action">Login</button>
			</div>
			<div class="mb-3 ">
				<a href="ForgotPassword.php" style="text-decoration: none; ; color:#0056b3">
				<center>Forgot Password</center>
				</a>
			</div>

			<p style="color:#8d8d8d">
				Don't have an account? 
				<a href="User_Registration.php" style="text-decoration: none; color:#0056b3">Sign Up</a>
			</p>
			
    </form>
</div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html> 





