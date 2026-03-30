<?php
// session_start();
require_once "session_config.php";

// Only ADMIN allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

// Database connection
$host = "localhost";
$dbname = "club_portal";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

// Fetch participation data
if ($event_id == 0) {

    // SHOW EVENTS LIST ONLY

    $stmt = $pdo->query("
        SELECT 
            e.event_id,
            e.event_name,
            e.event_date,
            COUNT(er.registration_id) AS total_participants
        FROM events e
        LEFT JOIN event_registrations er
            ON e.event_id = er.event_id
        GROUP BY e.event_id
        ORDER BY e.event_date DESC
    ");

    $events = $stmt->fetchAll();

} else {

    // SHOW PARTICIPANTS OF SELECTED EVENT

    $stmt = $pdo->prepare("
        SELECT 
            e.event_name,
            e.event_date,
            u.full_name,
            u.email,
            er.registered_at
        FROM event_registrations er
        JOIN users u ON er.user_id = u.user_id
        JOIN events e ON er.event_id = e.event_id
        WHERE e.event_id = ?
        ORDER BY er.registered_at DESC
    ");

    $stmt->execute([$event_id]);
    $participants = $stmt->fetchAll();

}

?>

<!DOCTYPE html>
<html>
<head>
    
<link rel="stylesheet" href="theme.css">
<title>Participation Reports | Club and Society Management Portal </title>
<link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">

<style>

body{
font-family:Segoe UI;
background: var(--bg-color);
color: var(--text-color);
margin:0;
min-height:100vh;
display:flex;
flex-direction:column;
}

.page-container{
flex:1;
padding:30px;
}

.header-bar{
display:flex;
align-items:center;
gap:12px;
padding:14px 30px;
background:var(--header-color);
color:white;
}

.header-left{
display:flex;
align-items:center;
gap:12px;
}

.logo{
width:60px;
height:60px;
border-radius:50%;
object-fit:cover;
}

.portal-title{
font-size:16px;
font-weight:600;
}

.back-btn{
display:inline-block;
margin-bottom:15px;
padding:10px 18px;
background: var(--header-color);
color:white;
text-decoration:none;
border-radius:8px;
font-weight:600;
transition:0.3s;
}

.back-btn:hover{
opacity:0.9;
transform:translateX(-3px);
}

.header{
background: var(--header-color);
color:white;
padding:15px;
border-radius:8px;
margin-bottom:20px;
}

.card{
background: var(--card-color);
color: var(--text-color);
padding:15px;
margin-bottom:12px;
border-radius:8px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
transition:0.3s;
}

.card:hover{
transform:translateY(-3px);
box-shadow:0 8px 18px rgba(0,0,0,0.15);
transition:0.2s;
}

.title{
font-size:18px;
font-weight:bold;
margin-bottom:5px;
}

.meta{
font-size:14px;
opacity:0.8;
}

.empty{
background: var(--card-color);
color: var(--text-color);
padding:20px;
border-radius:8px;
}

.footer{
text-align:center;
padding:18px;
font-size:14px;
background:var(--card-color);
color:var(--text-color);
border-top:1px solid rgba(0,0,0,0.1);
}

@media(max-width:768px){

body{
padding:15px;
}

.header h2{
font-size:18px;
}

.card{
padding:12px;
}

.title{
font-size:16px;
}


}

</style>

</head>
<body>

<div class="header-bar">

<div class="header-left">

<img src="adbu_app_logo_512x512.png" class="logo">

<div class="portal-title">
Club & Society Management Portal | Admin Dashboard
</div>

</div>

</div>

<div class="page-container">

<a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
<div class="header">
<h2>📊 Participation Reports</h2>
</div>


<?php if($event_id == 0): ?>

<!-- SHOW EVENTS LIST -->

<?php foreach($events as $e): ?>

<a href="participation_reports.php?event_id=<?php echo $e['event_id']; ?>" 
style="text-decoration:none;color:inherit;">

<div class="card">

<div class="title">
🏆 <?php echo htmlspecialchars($e['event_name']); ?>
</div>

<div class="meta">
📅 Date: <?php echo date("d M Y", strtotime($e['event_date'])); ?>
</div>

<div class="meta">
👥 Total Participants: <?php echo $e['total_participants']; ?>
</div>

</div>

</a>

<?php endforeach; ?>


<?php else: ?>

<!-- SHOW PARTICIPANTS -->

<a href="participation_reports.php" class="back-btn">⬅ Back to Events</a>

<?php if(empty($participants)): ?>

<div class="empty">
No participants registered for this event.
</div>

<?php else: ?>

<?php foreach($participants as $p): ?>

<div class="card">

<div class="title">
👤 <?php echo htmlspecialchars($p['full_name']); ?>
</div>

<div class="meta">
📧 <?php echo htmlspecialchars($p['email']); ?>
</div>

<div class="meta">
🕒 Registered: <?php echo date("d M Y", strtotime($p['registered_at'])); ?>
</div>

</div>

<?php endforeach; ?>

<?php endif; ?>

<?php endif; ?>

</div>

<footer class="footer">
<p>© <?php echo date("Y"); ?> Don Bosco University | Club & Society Management Portal | Developed by Jasmine & Manish</p>
</footer>

<script>
if(localStorage.getItem("mode")==="dark"){
    document.body.classList.add("dark");
}
</script>

</body>
</html>
