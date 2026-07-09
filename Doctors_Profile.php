<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Doctor Gallery | Hospital Management</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
  
	<!-- Bootstrap CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  
	<style>
		body {
			margin: 0;
			font-family: Georgia, serif;
			background: #f4f9f9;
		}

		/* Navbar Logo */
		.logo h2 {
			margin-left: 10px;
			color: #004b87;
		}

		.logo span {
			color: #00856a;
		}

		/* Gallery Header */
		.gallery-header {
			background-color: #007bff;
			color: white;
			padding: 40px 20px;
			text-align: center;
		}

		/* Doctor Card */
		.doctor-card {
			transition: transform 0.3s ease-in-out;
			border: 1px solid #ddd;
			border-radius: 12px;
			overflow: hidden;
			padding: 12px;
		}

		.doctor-card:hover {
			transform: translateY(-8px);
			box-shadow: 0 6px 15px rgba(0,0,0,0.15);
		}

		.doctor-card img {
			width: 100%;
			height: 100%;
			object-fit: cover;
			object-position: center;
			display: block;
		}

		.doctor-thumb-wrap {
			width: 100%;
			height: 190px;
			overflow: hidden;
			border-radius: 10px;
			margin: 0 0 12px;
			background: #f4f8fc;
		}

		.card-title {
			color: #004b87;
			font-size: 1.1rem;
			font-weight: bold;
			margin-top: 10px;
		}

		.card-specialization {
			color: #555;
			font-size: 0.95rem;
		}

		/* Footer */
		footer {
			margin-top: 40px;
		}
		/* Responsive Fix */
		@media (max-width: 768px) {
			.card-title {
				font-size: 1rem;
			}
			.card-specialization {
				font-size: 0.9rem;
			}
		}
	</style>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg hms-public-navbar sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="Index2.html">
        <img src="logo.png" width="40" height="40" alt="Logo" />
        <h2 class="hms-brand-title">
          <span class="brand-hms">HMS</span> <span class="brand-auto">Automanager</span>
        </h2>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="Index2.html">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="service.html">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="about.html">About Us</a></li>
          <li class="nav-item"><a class="nav-link active" href="Doctors_Profile.php">Doctors</a></li>
          <li class="nav-item"><a class="nav-link" href="gallery.html">Gallery</a></li>
          <li class="nav-item"><a class="nav-link" href="contect.php">Contact Us</a></li>
          <li class="nav-item nav-item-login"><a class="nav-link" href="Logins.html">Login</a></li>
        </ul>
      </div>
    </div>
</nav>

<!-- Gallery Header -->
<div class="gallery-header">
	<h1>Our Expert Doctors</h1>
	<p>Meet the professionals dedicated to your health</p>
</div>

<!-- Doctor Cards -->
<div class="container my-5">
	<div class="row g-4">
		<?php
			@include('config.php'); 
			$results = $conn->query("SELECT * FROM add_doctor");
		?>
		<?php while($data = $results->fetch_assoc()): ?>
			<div class="col-lg-4 col-md-6 col-sm-12">
				<div class="card doctor-card shadow-sm">
					<div class="doctor-thumb-wrap">
						<?php
							if($data['image'] == ''){
								echo '<img src="images/default-avatar.png" alt="Default Doctor">';
							}else{
								echo '<img src="uploaded_img/'.$data['image'].'" alt="Doctor Image">';
							}
						?>
					</div>
					<div class="card-body text-center">
						<p class="card-specialization"><?php echo $data['DoctorSpecialization'] ?></p>
						<h5 class="card-title"><?php echo $data['DoctorName'] ?></h5>
						
					</div>
				</div>
			</div>
		<?php endwhile; ?>
	</div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-3">
	<div class="container text-center">
		<p>© 2025 Smart Hospital Manager. All rights reserved.</p>
	</div>
</footer>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>









