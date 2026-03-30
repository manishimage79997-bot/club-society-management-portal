<?php
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

// DB connection
$conn = new mysqli("localhost", "root", "", "club_portal");  //Put your sql password here

// Get email from session
if (!isset($_SESSION['reset_email'])) {
    echo "error";
    exit();
}

$email = $_SESSION['reset_email'];

// Generate new OTP
$otp = rand(100000, 999999);

$_SESSION['otp'] = $otp;
$_SESSION['otp_time'] = time();

// Send email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mail->Username = 'manishimage79997@gmail.com';
    $mail->Password = 'ngyvopdewbaboams'; // 🔴 put your real one

    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('manishimage79997@gmail.com', 'Club Portal');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Resend OTP';
    $mail->Body = "<h3>Your new OTP is: $otp</h3>";

    $mail->send();

    echo "success";

} catch (Exception $e) {
    echo "error";
}
