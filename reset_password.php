<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "NITISH77", "club_portal");

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
        $message = "Unauthorized access!";
    } else {

        $new_password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Password length check
        if (strlen($new_password) < 6) {
            $message = "Password must be at least 6 characters!";
        }
        // Match check
        else if ($new_password !== $confirm_password) {
            $message = "Passwords do not match!";
        } 
        else {

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $email = $_SESSION['reset_email'];

            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);

            if ($stmt->execute()) {

                session_destroy();

                echo "<script>
                        alert('Password updated successfully!');
                        window.location.href='login_page.html';
                      </script>";
                exit();

            } else {
                $message = "Error updating password!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="theme.css">
    <meta charset="UTF-8">
    <title>Reset Password | Club Portal</title>
    <link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 40px;
        }

        .togglePassword {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
            transition: 0.2s;
        }

        .togglePassword:hover {
            color: #2a5298;
        }

        .match-message {
            font-size: 13px;
            margin-top: 5px;
            text-align: left;
        }

        .match-success {
            color: green;
        }

        .match-error {
            color: red;
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
        Club & Society Management Portal | Reset Password
    </div>
</div>


<div class="back-container">
    <a href="verify_otp.php" class="back-btn">⬅ Back</a>
</div>


<div class="wrapper">
    <div class="container">
        <h2>Set New Password</h2>

        <form method="POST">

            <div class="form-group password-group">
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter new password" required>
                    <span class="togglePassword">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="form-group password-group">
                <div class="password-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm password" required>
                    <span class="togglePassword">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
            </div>

            <div id="matchMessage" class="match-message"></div>

            <button type="submit" class="btn">Reset Password</button>

        </form>

        <div class="message"><?php echo $message; ?></div>
    </div>
</div>


<div class="footer">
© 2026 Don Bosco University | Club Portal | Developed by Jasmine & Manish
</div>
<script>

// PASSWORD SHOW / HIDE

const toggles = document.querySelectorAll(".togglePassword");

toggles.forEach(toggle => {

    const input = toggle.previousElementSibling;
    const icon = toggle.querySelector("i");

    toggle.addEventListener("click", () => {

        const type = input.type === "password" ? "text" : "password";
        input.type = type;

        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    });
});


// PASSWORD MATCH CHECK

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirm_password");
const matchMessage = document.getElementById("matchMessage");
const submitBtn = document.querySelector(".btn");

// Disable button initially
submitBtn.disabled = true;

function checkPasswordMatch() {

    if (confirmPassword.value === "") {
        matchMessage.textContent = "";
        submitBtn.disabled = true;
        return;
    }

    if (password.value === confirmPassword.value && password.value.length >= 6) {
        matchMessage.textContent = "✔ Passwords match";
        matchMessage.classList.add("match-success");
        matchMessage.classList.remove("match-error");

        submitBtn.disabled = false;
    } else {
        matchMessage.textContent = "❌ Passwords do not match";
        matchMessage.classList.add("match-error");
        matchMessage.classList.remove("match-success");

        submitBtn.disabled = true;
    }
}

// Trigger on typing
password.addEventListener("input", checkPasswordMatch);
confirmPassword.addEventListener("input", checkPasswordMatch);
</script>
</body>
</html>