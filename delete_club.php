<?php
// session_start();
require_once "session_config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root","NITISH77");

$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;

if ($club_id == 0) {
    die("Invalid Club ID");
}

try {

    // Delete memberships first
    $stmt = $pdo->prepare("DELETE FROM memberships WHERE club_id = ?");
    $stmt->execute([$club_id]);

    // Delete event registrations of club events
    $stmt = $pdo->prepare("
        DELETE er FROM event_registrations er
        JOIN events e ON er.event_id = e.event_id
        WHERE e.club_id = ?
    ");
    $stmt->execute([$club_id]);

    // Delete events
    $stmt = $pdo->prepare("DELETE FROM events WHERE club_id = ?");
    $stmt->execute([$club_id]);

    // Delete announcements
    $stmt = $pdo->prepare("DELETE FROM announcements WHERE club_id = ?");
    $stmt->execute([$club_id]);

    // Finally delete club
    $stmt = $pdo->prepare("DELETE FROM clubs WHERE club_id = ?");
    $stmt->execute([$club_id]);

    header("Location: admin_dashboard.php");
    exit();

} catch(PDOException $e){
    die("Delete failed: " . $e->getMessage());
}