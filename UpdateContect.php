<?php

include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('location:UserLogin.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];
$record_id = isset($_GET['id']) ? (int)$_GET['id'] : $user_id;

if(isset($_POST['update_profile'])){
	$update_name = mysqli_real_escape_string($conn, $_POST['update_name']);
	$update_phone = mysqli_real_escape_string($conn, $_POST['update_phone']);
	$update_email = mysqli_real_escape_string($conn, $_POST['update_email']);
	$update_subject = mysqli_real_escape_string($conn, $_POST['update_subject']);
	$update_message = mysqli_real_escape_string($conn, $_POST['update_message']);

	mysqli_query($conn, "UPDATE `contect_form` SET Name = '$update_name', Email = '$update_email', Subject = '$update_subject', MobileNumber = '$update_phone', Message = '$update_message' WHERE id = '$record_id'") or die('query failed');

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>update profile</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- custom css file link  -->
	<style>
		form{
			border:2px solid black;
			padding:20px;
			width:300px;
		}
		input{
			margin-top:10px;
		}
		@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600&display=swap');

		:root{
			--blue:#3498db;
			--dark-blue:#2980b9;
			--red:#e74c3c;
			--dark-red:#c0392b;
			--black:#333;
			--white:#fff;
			--light-bg:#eee;
			--box-shadow:0 5px 10px rgba(0,0,0,.1);
		}

		*{
						font-family: Georgia, serif;
			margin:0; padding:0;
			box-sizing: border-box;
			outline: none; border: none;
			text-decoration: none;
		}

		*::-webkit-scrollbar{
			width: 10px;
		}

		*::-webkit-scrollbar-track{
			background-color: transparent;
		}

		*::-webkit-scrollbar-thumb{
			background-color: var(--blue);
		}

		.btn,
		.delete-btn{
			width: 100%;
			border-radius: 5px;
			padding:10px 30px;
			color:var(--white);
			display: block;
			text-align: center;
			cursor: pointer;
			font-size: 20px;
			margin-top: 10px;
		}

		.btn{
			background-color: var(--blue);
		}

		.btn:hover{
			background-color: var(--dark-blue);
		}

		.delete-btn{
			background-color: var(--red);
		}

		.delete-btn:hover{
			background-color: var(--dark-red);
		}

		.message{
			margin:10px 0;
			width: 100%;
			border-radius: 5px;
			padding:10px;
			text-align: center;
			background-color: var(--red);
			color:var(--white);
			font-size: 20px;
		}

		.form-container{
			min-height: 100vh;
			background-color: var(--light-bg);
			display: flex;
			align-items: center;
			justify-content: center;
			padding:20px;
		}

		.form-container form{
			padding:20px;
			background-color: var(--white);
			box-shadow: var(--box-shadow);
			text-align: center;
			width: 500px;
			border-radius: 5px;
		}

		.form-container form h3{
			margin-bottom: 10px;
			font-size: 30px;
			color:var(--black);
			text-transform: uppercase;
		}

		.form-container form .box{
			width: 100%;
			border-radius: 5px;
			padding:12px 14px;
			font-size: 18px;
			color:var(--black);
			margin:10px 0;
			background-color: var(--light-bg);
		}

		.form-container form p{
			margin-top: 15px;
			font-size: 20px;
			color:var(--black);
		}

		.form-container form p a{
			color:var(--red);
		}

		.form-container form p a:hover{
			text-decoration: underline;
		}

		.container{
			min-height: 100vh;
			background-color: var(--light-bg);
			display: flex;
			align-items: center;
			justify-content: center;
			padding:20px;
		}

		.container .profile{
			padding:20px;
			background-color: var(--white);
			box-shadow: var(--box-shadow);
			text-align: center;
			width: 400px;
			border-radius: 5px;
		}

		.container .profile img{
			height: 150px;
			width: 150px;
			border-radius: 50%;
			object-fit: cover;
			margin-bottom: 5px;
		}

		.container .profile h3{
			margin:5px 0;
			font-size: 20px;
			color:var(--black);
		}

		.container .profile p{
			margin-top: 20px;
			color:var(--black);
			font-size: 20px;
		}

		.container .profile p a{
			color:var(--red);
		}

		.container .profile p a:hover{
			text-decoration: underline;
		}

		.update-profile{
			min-height: 100vh;
			background-color: var(--light-bg);
			display: flex;
			align-items: center;
			justify-content: center;
			padding:20px;
		}

		.update-profile form{
			padding:20px;
			background-color: var(--white);
			box-shadow: var(--box-shadow);
			text-align: center;
			width: 700px;
			text-align: center;
			border-radius: 5px;
		}

		.update-profile form img{
			height: 200px;
			width: 200p;
			border-radius: 50%;
			object-fit: cover;
			margin-bottom: 5px;
		}

		.update-profile form .flex{
			display: flex;
			justify-content: space-between;
			margin-bottom: 20px;
			gap:15px;
		}

		.update-profile form .flex .inputBox{
			width: 49%;
		}

		.update-profile form .flex .inputBox span{
			text-align: left;
			display: block;
			margin-top: 15px;
			font-size: 17px;
			color:var(--black);
		}

		.update-profile form .flex .inputBox .box{
			width: 100%;
			border-radius: 5px;
			background-color: var(--light-bg);
			padding:12px 14px;
			font-size: 17px;
			color:var(--black);
			margin-top: 10px;
		}

		@media (max-width:650px){
		   .update-profile form .flex{
				flex-wrap: wrap;
				gap:0;
		   }
		   .update-profile form .flex .inputBox{
				width: 100%;
		   }
		}
	</style>
	
   

	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>
   
<div class="update-profile">
	<?php
		$select = mysqli_query($conn, "SELECT * FROM `contect_form` WHERE id = '$record_id'") or die('query failed');
		if(mysqli_num_rows($select) > 0){
			$fetch = mysqli_fetch_assoc($select);
		} else {
            $fetch = ['Name' => '', 'Email' => '', 'Subject' => '', 'MobileNumber' => '', 'Message' => ''];
		}
	?>
      
   <form action="" method="post" enctype="multipart/form-data">
		<div class="flex">
			<div class="inputBox">
				<span>NAME:</span><br>
				<input type="text" name="update_name" value="<?php echo $fetch['Name']; ?>" class="box"><br>
				<span>Email :</span><br>
				<input type="email" name="update_email" value="<?php echo $fetch['Email']; ?>" class="box"><br>
				<span>	SUBJECT :</span><br>
				<input type="text" name="update_subject" value="<?php echo $fetch['Subject']; ?>" class="box"><br>
			</div>
			<div class="inputBox">
				<span> MOBILENUMBER :</span><br>
				<input type="tel" name="update_phone" value="<?php echo $fetch['MobileNumber']; ?>" class="box" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required><br>
				<span>Message :</span><br>
				<input type="text" name="update_message"  value="<?php echo $fetch['Message']; ?>" class="box"><br>
			</div>
      </div>
      <input type="submit" value="update profile" name="update_profile" class="btn btn-secondary-action">
      <a href="FetchContect.php" class="delete-btn btn-danger-action">go back</a>
   </form>

</div>

</body>
</html>





