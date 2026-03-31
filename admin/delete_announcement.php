<?php
// session_start();
require_once "session_config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root","NITISH77");

$announcement_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($announcement_id == 0) {
    die("Invalid Announcement ID");
}

$stmt = $pdo->prepare("DELETE FROM announcements WHERE announcement_id = ?");
$stmt->execute([$announcement_id]);

header("Location: admin_dashboard.php");
exit();