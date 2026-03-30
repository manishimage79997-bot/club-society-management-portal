<?php
// session_start();
require_once "session_config.php";


if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root","");

$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'];

// Check if already registered
$check = $pdo->prepare("
SELECT * FROM event_registrations
WHERE event_id = ? AND user_id = ?
");

$check->execute([$event_id,$user_id]);

if($check->rowCount() == 0){

$stmt = $pdo->prepare("
INSERT INTO event_registrations (event_id,user_id)
VALUES (?,?)
");

$stmt->execute([$event_id,$user_id]);

}

header("Location: student_dashboard.php");
exit();
?>
