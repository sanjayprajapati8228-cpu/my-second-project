<?php
@include('config.php');

// Check if ID is received
if (isset($_POST['id'])) {
    $id = intval($_POST['id']); // Convert input to integer for safety
	
    // Check if ID exists in the database
    $check_sql = "SELECT * FROM user_appointments WHERE id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ID exists, proceed to delete
        $delete_sql = "DELETE FROM user_appointments  WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();

        echo "<script>alert('Record deleted successfully'); window.location.href='FeatchAppointments.php';</script>";
    } else {
        // ID does not exist
        echo "<script>alert('Your ID does not match');</script>";
    }

    $stmt->close();
    $conn->close();
} 
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>cancel Button</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		body {
			font-family: Georgia, serif;
			padding: 80px;
			background-color: #f4f4f4;
		}

		.form-container {
			display: flex;
			gap: 10px;
			align-items: center;
						justify-content:center;

		}

		input[type="number"] {
			padding: 10px;
			font-size: 16px;
			border: 2px solid #ccc;
			border-radius: 4px;
			width: 250px;
		}

		button {
			padding: 10px 20px;
			background-color: #e74c3c;
			color: white;
			border: none;
			border-radius: 4px;
			font-size: 16px;
			cursor: pointer;
		}

		button:hover {
			background-color: #c0392b;
		}
  </style>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="hms-ui-consistency.css" />
	<link rel="stylesheet" href="hms-theme.css" />
	<link rel="stylesheet" href="css/buttons.css" />
</head>
<body>
<form method="post" action="">
	<div class="form-container">
		<input type="number" min="1" name="id" placeholder="Enter Id" required>
		<button type="submit" class="btn-danger-action">Delete</button>
	</div>
	</form>
		<?php if (!empty($message)): ?>
			<script>
				alert("<?php echo addslashes($message); ?>");
			</script>
		<?php endif; ?>
</body>
</html>






