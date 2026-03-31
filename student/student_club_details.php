<?php
// session_start();
require_once "session_config.php";


$preview = isset($_GET['preview']);

// STUDENT ONLY ACCESS
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "STUDENT") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root","");  //Put your sql password here

$user_id = $_SESSION['user_id'];

// VALIDATE CLUB ID
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;

if ($club_id == 0) {
    echo "Invalid Club ID";
    exit();
}

// CHECK IF STUDENT IS MEMBER
$stmt = $pdo->prepare("
SELECT * FROM memberships
WHERE club_id=? AND user_id=? AND status='APPROVED'
");
$stmt->execute([$club_id, $user_id]);

if ($stmt->rowCount() == 0) {
    echo "Access Denied";
    exit();
}


// FETCH CLUB INFO
$stmt = $pdo->prepare("
SELECT club_name, category, description, president, created_at
FROM clubs
WHERE club_id=?
");
$stmt->execute([$club_id]);
$club = $stmt->fetch();

if (!$club) {
    echo "Club not found";
    exit();
}

// FETCH CLUB ANNOUNCEMENTS
$stmt = $pdo->prepare("
SELECT a.*, u.full_name
FROM announcements a
JOIN users u ON u.user_id = a.posted_by
WHERE a.club_id=?
ORDER BY a.created_at DESC
");
$stmt->execute([$club_id]);
$announcements = $stmt->fetchAll();


// FETCH CLUB EVENTS
$stmt = $pdo->prepare("
SELECT e.*, er.status AS registration_status
FROM events e
LEFT JOIN event_registrations er
    ON e.event_id = er.event_id 
    AND er.user_id = ?
WHERE e.club_id = ?
ORDER BY e.event_date DESC
");

$stmt->execute([$user_id, $club_id]);
$events = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="theme.css">
<title> Club Details | Club and Society Management Portal </title>
<link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">

<style>

body{
    font-family:Segoe UI;
    background: var(--bg-color);
    color: var(--text-color);
    margin: 0;
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

.header{
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

.portal-title small{
font-size:12px;
opacity:0.8;
}

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 18px;
    background: var(--header-color);
    color:white;
    text-decoration:none;
    border-radius:8px;
    font-weight:600;
    transition:0.3s;
}

.back-btn:hover{
    background:#1c3b47;
    transform:translateX(-3px);
    box-shadow:0 4px 12px rgba(0,0,0,0.2);
}

.card{
    background: var(--card-color);
    color: var(--text-color);
    padding:20px;
    border-radius:10px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

.card:hover{
    transform: translateY(-4px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.12);
}

.announcement-card{
    background: var(--card-color);
    color: var(--text-color);
    padding:15px;
    border-radius:8px;
    margin-bottom:15px;
    box-shadow:0 3px 8px rgba(0,0,0,0.08);
    transition:0.3s;
}

.announcement-card:hover{
    transform:translateY(-4px);
    box-shadow:0 8px 18px rgba(0,0,0,0.12);
}

.event-btn{
    padding:8px 15px;
    border:none;
    border-radius:6px;
    color:white;
    cursor:pointer;
    transition:0.3s;
}

.register-btn{
    background:#007bff;
}

.register-btn:hover{
    background:#0056b3;
    transform:translateY(-2px);
}

.registered-btn{
    background:green;
}

.completed-btn{
    background:orange;
}

.page-container{
    flex: 1;
    padding:30px;
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
    .page-container{
        padding:15px;
    }
}

</style>
</head>
<body>

<div class="header">

<div class="header-left">

<img src="adbu_app_logo_512x512.png" class="logo">

<div class="portal-title">
Club & Society Management Portal
<br>
<small>Student Dashboard</small>
</div>

</div>

</div>

<div class="page-container">

<a href="javascript:history.back()" class="back-btn">⬅ Back to Dashboard</a>


<div class="card">

<h2><?php echo htmlspecialchars($club['club_name']); ?></h2>

<p><b>Category:</b> <?php echo htmlspecialchars($club['category']); ?></p>

<p><b>President:</b> <?php echo htmlspecialchars($club['president']); ?></p>

<p><b>Description:</b> <?php echo htmlspecialchars($club['description']); ?></p>

<p><b>Created:</b> <?php echo date("d M Y", strtotime($club['created_at'])); ?></p>

</div>

<h3>Club Announcements</h3>

<?php if(count($announcements) > 0): ?>

    <?php foreach($announcements as $a): ?>

        <div class="announcement-card">

            <h4><?php echo htmlspecialchars($a['title']); ?></h4>

            <p><?php echo nl2br(htmlspecialchars($a['body'])); ?></p>

            <small>
                Posted by <?php echo htmlspecialchars($a['full_name']); ?> 
                on <?php echo date("d M Y", strtotime($a['created_at'])); ?>
            </small>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="card">
        No announcements for this club yet.
    </div>

<?php endif; ?>

<h3>Club Events</h3>

<?php if(!empty($events)): ?>

    <?php foreach($events as $event): ?>

        <div class="card">

            <h4><?php echo htmlspecialchars($event['event_name']); ?></h4>

            <p><?php echo htmlspecialchars($event['event_description']); ?></p>

            <p><b>Date:</b> <?php echo htmlspecialchars($event['event_date']); ?></p>

            <p><b>Time:</b> <?php echo htmlspecialchars($event['event_time']); ?></p>

            <p><b>Location:</b> <?php echo htmlspecialchars($event['event_location']); ?></p>

            <?php $today = date("Y-m-d"); ?>

            <?php if($event['event_date'] < $today): ?>

                <button disabled class="event-btn completed-btn">
                    Event Completed
                </button>

            <?php elseif($event['registration_status'] == 'REGISTERED'): ?>

                <button disabled class="event-btn registered-btn">
                    Registration Done
                </button>

            <?php else: ?>

            <?php if($preview): ?>

<button disabled class="event-btn completed-btn">
Preview Mode
</button>

<?php else: ?>

<form method="POST" action="join_event.php">
    <input type="hidden" name="event_id"
           value="<?php echo $event['event_id']; ?>">
    <button class="event-btn register-btn">
        Register
    </button>
</form>

<?php endif; ?>

            <?php endif; ?>

        </div>

    <?php endforeach; ?>

<?php else: ?>

    <div class="card">
        No events for this club yet.
    </div>

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
