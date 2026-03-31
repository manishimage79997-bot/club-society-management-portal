<?php
// Database connection file

$host = "localhost";
$username = "root";
$password = "NITISH77";
$database = "club_portal";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>