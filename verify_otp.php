<?php
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_otp = trim($_POST['otp']);

    if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_time'])) {
        $message = "Session expired. Please try again.";
    } else {

        // Expiry check (5 min)
        if (time() - $_SESSION['otp_time'] > 300) {
            $message = "OTP expired! Please request again.";
            session_destroy();
        } 
        else if ($user_otp == $_SESSION['otp']) {

            $_SESSION['otp_verified'] = true;

            header("Location: reset_password.php");
            exit();

        } else {
            $message = "Invalid OTP!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="theme.css">
    <meta charset="UTF-8">
    <title>Verify OTP | Club Portal</title>
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
            text-align: center;
            letter-spacing: 3px;
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

        .otp-boxes {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .otp-input {
            width: 45px;
            height: 50px;
            text-align: center;
            font-size: 18px;
            border-radius: 8px;
            border: 1px solid #ccc;
            transition: 0.2s;
        }

        .otp-input:focus {
            border-color: #2a5298;
            outline: none;
            transform: scale(1.05);
        }

        .otp-input:hover {
            border-color: #2a5298;
        }

        .otp-timer {
            text-align: center;
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }

        .resend-btn {
            width: 100%;
            margin-top: 10px;
            padding: 10px;
            background: #ccc;
            color: #333;
            border: none;
            border-radius: 6px;
            cursor: not-allowed;
            transition: 0.3s;
        }

        .resend-btn.active {
            background: #2a5298;
            color: white;
            cursor: pointer;
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

<!-- HEADER -->
<div class="header">
    <img src="adbu_app_logo_512x512.png" class="logo">
    <div class="portal-title">
        Club & Society Management Portal | Verify OTP
    </div>
</div>

<!-- BACK BUTTON -->
<div class="back-container">
    <a href="forgot_password.php" class="back-btn">⬅ Back</a>
</div>

<!-- BODY -->
<div class="wrapper">
    <div class="container">
        <h2>Enter OTP</h2>

        <form method="POST">

            <div class="form-group">
                <div class="otp-boxes">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                    <input type="text" maxlength="1" class="otp-input">
                </div>

                <input type="hidden" name="otp" id="finalOtp">
            </div>

            <button type="submit" class="btn">Verify OTP</button>

            <div class="otp-timer">
                Resend OTP in <span id="timer">10</span>s
            </div>

            <button type="button" id="resendBtn" class="resend-btn" disabled>
                Resend OTP
            </button>
        </form>

        <div class="message"><?php echo $message; ?></div>
    </div>
</div>

<!-- FOOTER -->
<div class="footer">
© 2026 Don Bosco University | Club Portal | Developed by Jasmine & Manish
</div>

<script>
// OTP INPUT HANDLING
const inputs = document.querySelectorAll(".otp-input");
const finalOtp = document.getElementById("finalOtp");

// Auto focus first box
inputs[0].focus();

inputs.forEach((input, index) => {

    input.addEventListener("input", () => {

        // Allow only numbers
        input.value = input.value.replace(/[^0-9]/g, "");

        // Move to next box
        if (input.value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }

        updateOTP();
    });

    // Backspace handling
    input.addEventListener("keydown", (e) => {
        if (e.key === "Backspace" && input.value === "" && index > 0) {
            inputs[index - 1].focus();
        }
    });
});

// Paste support (paste full OTP)
inputs[0].addEventListener("paste", (e) => {
    let pasteData = e.clipboardData.getData("text").slice(0, 6);

    inputs.forEach((input, i) => {
        input.value = pasteData[i] || "";
    });

    updateOTP();
});

// Combine OTP into hidden field
function updateOTP() {
    let otp = "";
    inputs.forEach(input => otp += input.value);
    finalOtp.value = otp;
}

//////////////////////////////////////////////////////
// TIMER + RESEND OTP
//////////////////////////////////////////////////////

let timeLeft = 10;
const timerElement = document.getElementById("timer");
const resendBtn = document.getElementById("resendBtn");

let countdown;

// Start Timer Function
function startTimer() {

    timeLeft = 10; // reset here

    resendBtn.disabled = true;
    resendBtn.classList.remove("active");

    countdown = setInterval(() => {
        timeLeft--;
        timerElement.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(countdown);
            resendBtn.disabled = false;
            resendBtn.classList.add("active");
            timerElement.textContent = "0";
        }
    }, 1000);
}

// Start timer initially
startTimer();

// Resend OTP click
resendBtn.addEventListener("click", () => {

    fetch("resend_otp.php")
    .then(response => response.text())
    .then(data => {
        if (data === "success") {
            alert("OTP Resent Successfully!");
        } else {
            alert("Error sending OTP!");
        }
    });

    clearInterval(countdown); // stop old timer
    startTimer(); // restart cleanly
});
</script>

</body>
</html>