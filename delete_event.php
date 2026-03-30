<?php
// session_start();
require_once "session_config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root",""); //Put your sql password here

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id == 0) {
    die("Invalid Event ID");
}

try {

    // Delete registrations first
    $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE event_id = ?");
    $stmt->execute([$event_id]);

    // Delete event
    $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);

    header("Location: admin_dashboard.php");
    exit();

} catch(PDOException $e){
    die("Delete failed: " . $e->getMessage());
}
