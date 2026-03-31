<?php
// session_start();
require_once "session_config.php";
require "database_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password_hash'])) {

            // Store session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            // Role-based redirection
            if ($user['role'] == "ADMIN") {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: student_dashboard.php");
            }

        } else {
            echo "Invalid password.";
        }

    } else {
        echo "User not found.";
    }

    $stmt->close();
}
?>