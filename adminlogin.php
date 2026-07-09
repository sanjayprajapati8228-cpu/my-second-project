
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Admin Login Form</title>
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
<div action="" class="form-container">
	<form method="POST" action="adminlogin1.php">
		<div class="mb-3">
			<center> <h1>Log in</h1> </center>
        </div>
		<div class="mb-3">
			<label class="form-label" for="userid">User Id</label><br>
			<input class="form-control" type="text" id="userId" name="userid" placeholder="User Id " required>
        </div>
		<div class="mb-3">
			<label for="password" class="form-label">Password</label>
			<input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
      
        <a href="Logins.html" style="text-decoration: none; color:#0056b3">&larr; Go Back</a>
			
      
	<div class="d-grid mb-3">
  <button type="submit" class="btn w-100 btn-primary-action">Login</button>
</div>
 
      
	</form>
</div>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>





