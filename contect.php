<?php
    // Include the database configuration
    include 'config.php';

    // Set charset for special characters (important for local languages)
    mysqli_set_charset($conn, "utf8mb4");

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        
        // 1. Sanitize input to prevent SQL Injection
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $subject_form = mysqli_real_escape_string($conn, $_POST['subject']);
        $phone = mysqli_real_escape_string($conn, $_POST['number']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);

        // 2. Database Insert 
        // NOTE: Ensure your table name is 'contect_form' in phpMyAdmin
        $insert_query = "INSERT INTO contect_form (Name, Email, Subject, MobileNumber, Message) 
                         VALUES ('$name', '$email', '$subject_form', '$phone', '$message')";
        
        $query_run = mysqli_query($conn, $insert_query);

        if ($query_run) {
            // 3. Email Logic
            $to = $email;
            $email_subject = "Thank you for contacting HMS Automanager";
            $email_body = "
            <html><head><meta charset='UTF-8'><title>Thanks for Contacting HMS</title></head>
            <body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
            <table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
            <table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
            <tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>Thanks for Contacting HMS</td></tr>
            <tr><td style='padding:24px;line-height:1.7;'><p>Dear $name,</p><p>We have received your message regarding: <strong>$subject_form</strong>.</p><p>Our team will contact you shortly.</p></td></tr>
            <tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS Support Team</td></tr>
            </table></td></tr></table></body></html>";
            
            // Basic headers
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: HMS Support <gujaratijeel15@gmail.com>\r\n";
            $headers .= "Reply-To: unityhospital@gmail.com\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Attempt to send email (Note: This requires a configured SMTP server like XAMPP Sendmail or a live host)
            @mail($to, $email_subject, $email_body, $headers);

            // Redirect to show success message
            header("Location: contect.php?status=success");
            exit();
        } else {
            // Error handling
            header("Location: contect.php?status=error");
            exit();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Hospital Manager - Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Georgia, serif; background: #f4f9f9; margin: 0; }
        .logo h2 { margin-left: 10px; color: #004b87; }
        .logo span { color: #00856a; }
        .footer { background: #002849; color: white; padding: 3%; display: flex; justify-content: space-around; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="hms-ui-consistency.css" />
    <link rel="stylesheet" href="hms-theme.css" />
    <link rel="stylesheet" href="css/buttons.css">
</head>
<body>

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
          <li class="nav-item"><a class="nav-link" href="Doctors_Profile.php">Doctors</a></li>
          <li class="nav-item"><a class="nav-link" href="gallery.html">Gallery</a></li>
          <li class="nav-item"><a class="nav-link active" href="contect.php">Contact Us</a></li>
          <li class="nav-item nav-item-login"><a class="nav-link" href="Logins.html">Login</a></li>
        </ul>
      </div>
    </div>
</nav>

<section class="container my-5">
    <?php
        // Show alerts based on the URL status parameter
        if(isset($_GET['status'])){
            if($_GET['status'] == 'success'){
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        <strong>Success!</strong> Your message has been sent successfully!
                        <button type="button" class="btn-close btn-secondary-action" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            } elseif($_GET['status'] == 'error'){
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Error!</strong> Message could not be saved. Please check your database connection.
                        <button type="button" class="btn-close btn-secondary-action" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
    ?>
    <div class="row">
        <div class="col-lg-8">
            <h2>Contact Form</h2>
            <form action="contect.php" method="POST" class="row g-3">
                <div class="col-md-12">
                    <label>Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-12">
                    <label>E-mail</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="col-12">
                    <label>Subject</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-tag"></i></span>
                        <input type="text" name="subject" class="form-control">
                    </div>
                </div>
                <div class="col-12">
                    <label>Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                        <input type="tel" name="number" class="form-control" pattern="[0-9]{10}" minlength="10" maxlength="10" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10);" required>
                    </div>
                </div>
                <div class="col-12">
                    <label>Message</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-comment"></i></span>
                        <textarea name="message" class="form-control" rows="4"></textarea>
                    </div>
                </div>
                <div class="col-12">
                    <button type="submit" name="submit" class="btn btn-primary btn-primary-action">Send message</button>
                </div>
            </form>
        </div>
        <div class="col-lg-4">
            <h2>Address</h2>
            <ul class="list-unstyled">
                <li><i class="fa fa-home"></i> Ahmedabad, IN.</li>
                <li><i class="fa fa-envelope"></i> unityhospital@gmail.com</li>
                <li><i class="fa fa-phone"></i> 886 666 00555</li>
                <li><i class="fa fa-globe"></i> www.UnityHospital.com</li>
            </ul>
            <h2>Business Hours</h2>
            <ul>
                <li><strong>Monday-Saturday:</strong> 24/7 Available</li>
                <li><strong>Sunday:</strong> 4 AM to 11 PM</li>
            </ul>
        </div>
    </div>
</section>

<footer class="bg-dark text-white py-4 mt-4">
    <div class="container text-center">
          <p>© 2026 HMS Automanager. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>








