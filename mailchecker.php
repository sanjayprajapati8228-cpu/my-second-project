<?php
$to = "your_personal_email@gmail.com";
$subject = "Test Mail";
$message = "
<html><head><meta charset='UTF-8'><title>Test Mail</title>`r`n</head>
<body style='margin:0;padding:0;background:#eef4f8;font-family:Arial,sans-serif;color:#133042;'>
<table width='100%' cellpadding='0' cellspacing='0' style='padding:24px 10px;'><tr><td align='center'>
<table width='620' cellpadding='0' cellspacing='0' style='max-width:620px;background:#fff;border-radius:14px;overflow:hidden;border:1px solid #d9e6f1;'>
<tr><td style='background:linear-gradient(90deg,#002849,#0b79a5);padding:18px 24px;color:#fff;font-size:20px;font-weight:700;'>HMS Mail Test</td></tr>
<tr><td style='padding:24px;line-height:1.7;'><p>This is a styled test email from HMS mail checker.</p></td></tr>
<tr><td style='padding:14px 24px;background:#f2f7fb;color:#5a7386;font-size:12px;'>HMS System</td></tr>
</table></td></tr></table></body></html>";
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: HMS Test <gujaratijeel15@gmail.com>\r\n";

if(mail($to, $subject, $message, $headers)) {
    echo "Mail Sent Successfully";
} else {
    echo "Mail Failed";
}
?>


