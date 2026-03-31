<?php
// session_start();
require_once "session_config.php";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "STUDENT") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root","");  // put your sql password here

$user_id = $_SESSION['user_id'];

$event_id = intval($_GET['event_id'] ?? 0);

if ($event_id <= 0) {
    exit("Invalid Event");
}

// Fetch event + club name
$stmt = $pdo->prepare("
SELECT e.*, c.club_name
FROM events e
LEFT JOIN clubs c ON e.club_id = c.club_id
WHERE e.event_id = ?
");

$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    exit("Event not found");
}

// If club-specific event, check membership
if ($event['club_id'] != NULL) {

    $check = $pdo->prepare("
        SELECT * FROM memberships
        WHERE user_id = ?
        AND club_id = ?
        AND status = 'APPROVED'
    ");

    $check->execute([$user_id, $event['club_id']]);

    if ($check->rowCount() == 0) {
        exit("Access Denied");
    }
}

$reg = $pdo->prepare("
SELECT * FROM event_registrations
WHERE user_id = ?
AND event_id = ?
");

$reg->execute([$user_id, $event_id]);
$alreadyRegistered = $reg->rowCount() > 0;

?>
<!DOCTYPE html>
<html>
<head>
<title>Event Details</title>
<link rel="stylesheet" href="theme.css">
<style>
body{
    font-family:Segoe UI;
    background:#f4f6f9;
    padding:30px;
}
.card{
    background:white;
    padding:25px;
    border-radius:10px;
    max-width:600px;
}
button{
    padding:10px 15px;
    border:none;
    background:#007bff;
    color:white;
    cursor:pointer;
}
.registered{
    background:green;
}
</style>
</head>
<body>

<div class="card">

<h2><?php echo htmlspecialchars($event['event_name']); ?></h2>

<p><b>Description:</b> <?php echo htmlspecialchars($event['event_description']); ?></p>

<p><b>Date:</b> <?php echo htmlspecialchars($event['event_date']); ?></p>

<p><b>Time:</b> <?php echo htmlspecialchars($event['event_time']); ?></p>

<p><b>Location:</b> <?php echo htmlspecialchars($event['event_location']); ?></p>

<?php if($event['club_id'] != NULL): ?>
<p><b>Club:</b> <?php echo htmlspecialchars($event['club_name']); ?></p>
<?php else: ?>
<p><b>Type:</b> Global Event</p>
<?php endif; ?>

<br>

<?php if($alreadyRegistered): ?>
<button class="registered">Already Registered</button>
<?php else: ?>
<form method="POST" action="join_event.php">
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
    <button type="submit">Register</button>
</form>
<?php endif; ?>

</div>

</body>
</html>
