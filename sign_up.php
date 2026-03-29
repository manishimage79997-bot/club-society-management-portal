<?php
require "database_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    $sql = "INSERT INTO users (full_name, email, phone_number, password_hash, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $full_name, $email, $phone, $password_hash, $role);

    if ($stmt->execute()) {
        header("Location: login_page.html");
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>