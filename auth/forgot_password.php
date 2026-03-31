<?php
session_start();

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// DB connection
$conn = new mysqli("localhost", "root", "", "club_portal");  //Put your sql password here

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Trim inputs
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Validate phone (10 digits)
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $message = "Enter valid 10-digit phone number!";
    } else {

        // Check email + phone_number
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND phone_number = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            // Generate OTP
            $otp = rand(100000, 999999);

            $_SESSION['reset_email'] = $email;
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_time'] = time();

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = ''; // put your email here
                $mail->Password = ''; // put your 2 step verification passkey here

                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('manishimage79997@gmail.com', 'Club Portal');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "<h3>Your OTP is: $otp</h3><p>Valid for 5 minutes.</p>";

                $mail->send();

                header("Location: verify_otp.php");
                exit();

            } catch (Exception $e) {
                $message = "Error sending email: " . $mail->ErrorInfo;
            }

        } else {
            $message = "Invalid email or phone number!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="theme.css">
    <meta charset="UTF-8">
    <title>Forgot Password | Club Portal</title>
    <link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #f0f4ff, #d6e4f7);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 30px;
            background: #0f2d54;
            color: white;
        }

        .logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
        }

        .portal-title {
            font-size: 22px;
            font-weight: 600;
        }

        .back-container {
            padding: 20px 30px;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 18px;
            background: #0f2d54;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #1c3b47;
            transform: translateX(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .wrapper {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
        }

        .container {
            background: #fff;
            width: 100%;
            max-width: 400px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        input {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        input:focus {
            border-color: #2a5298;
            outline: none;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #2a5298;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .btn:hover {
            background: #1e3c72;
        }

        .message {
            color: red;
            margin-top: 10px;
            text-align: center;
        }

        .footer {
            width: 100%;
            text-align: center;
            padding: 15px;
            background: #0f2d54;
            color: #ccc;
            margin-top: auto;
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }
        }
    </style>
</head>

<body>


<div class="header">
    <img src="adbu_app_logo_512x512.png" class="logo">
    <div class="portal-title">
        Club & Society Management Portal | Forgot Password
    </div>
</div>


<div class="back-container">
    <a href="login_page.html" class="back-btn">⬅ Back to Login</a>
</div>


<div class="wrapper">
    <div class="container">
        <h2>Reset Your Password</h2>

        <form method="POST">

            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="form-group">
                <input type="text" name="phone" placeholder="Enter your phone number" required>
            </div>

            <button type="submit" class="btn">Send OTP</button>

        </form>

        <div class="message"><?php echo $message; ?></div>
    </div>
</div>


<div class="footer">
© 2026 Don Bosco University | Club Portal | Developed by Jasmine & Manish
</div>

</body>
</html>
