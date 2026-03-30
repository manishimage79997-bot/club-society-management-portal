<?php
// session_start();
require_once "session_config.php";


$preview = isset($_GET['preview']);

// Restrict access
if (!isset($_SESSION['user_id'])) {
    header("Location: login_page.html");
    exit();
}

if ($_SESSION['role'] != "STUDENT" && !$preview) {
    echo "Access Denied";
    exit();
}

$host = "localhost";
$dbname = "club_portal";
$user = "root";
$pass = "";  //Put your sql password here

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

$user_id = $_SESSION['user_id'];
$name = $_SESSION['full_name'];



// Fetch clubs with membership status
$stmt = $pdo->prepare("
SELECT c.*, m.status, m.join_attempts, m.rejected_at
FROM clubs c
LEFT JOIN memberships m 
ON c.club_id = m.club_id AND m.user_id = ?
");

$stmt->execute([$user_id]);
$clubs = $stmt->fetchAll();

// Fetch announcements
$stmt = $pdo->prepare("
SELECT a.*, u.full_name 
FROM announcements a
JOIN users u ON a.posted_by = u.user_id
WHERE a.club_id IS NULL
ORDER BY a.created_at DESC
");

$stmt->execute();
$announcements = $stmt->fetchAll();

// FETCH JOINED CLUBS
$stmt = $pdo->prepare("
SELECT c.club_id, c.club_name, c.description, c.category
FROM clubs c
JOIN memberships m ON c.club_id = m.club_id
WHERE m.user_id = ? AND m.status = 'APPROVED'
");

$stmt->execute([$user_id]);
$joined_clubs = $stmt->fetchAll();

// FETCH EVENTS WITH REGISTRATION STATUS
$stmt = $pdo->prepare("
SELECT e.*, er.status AS registration_status
FROM events e
LEFT JOIN event_registrations er
    ON e.event_id = er.event_id 
    AND er.user_id = ?
WHERE e.club_id IS NULL
ORDER BY e.event_date DESC
");

$stmt->execute([$user_id]);
$events = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="theme.css">
<title>Student Dashboard | Club and Society Management Portal </title>
<link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">


<style>

body{
font-family:Segoe UI;
margin:0;
}

.header{
color:white;
padding:14px 30px;
display:flex;
justify-content:space-between;
align-items:center;
}

.header-left{
display:flex;
align-items:center;
gap:12px;
}

.card2 p,
.card3 p,
.card4 p{
word-wrap:break-word;
overflow-wrap:break-word;
margin:11px 0;
}

.section a{
    flex:1 1 100px;
}

.logo{
width:60px;
height:60px;
border-radius:50%;
}

.portal-title{
font-weight:600;
font-size:16px;
line-height:1.2;
}

.portal-title span{
font-size:12px;
opacity:0.8;
}

.header-right{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;   
}

.container{
    padding: 20px;
}

.card1{
padding:20px;
margin-bottom:15px;
margin: 15px 0;
border-radius:8px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

.card2{
padding:20px;
margin-bottom:15px;
margin: 0;
border-radius:8px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

.card3{
padding:20px;
margin-bottom:15px;
margin: 10px;
border-radius:8px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

.card4{
padding:20px;
margin-bottom:15px;
margin: 10px;
border-radius:8px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

body,
.card1,
.card2,
.card3,
.card4,
.header{
transition: background 0.3s ease, color 0.3s ease;
}

/* Common hover effect for all cards */
.card1,
.card2,
.card3,
.card4 {
    background: var(--card-color);
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.card1,
.card2,
.card3,
.card4{
min-height:100px;
}

.card1:hover,
.card2:hover,
.card3:hover,
.card4:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.12);
}

.btn{
padding:8px 15px;
border:none;
border-radius:5px;
cursor:pointer;
width:auto;
align-self:flex-start; 
transition: all 0.3s ease;
}

.btn:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

.toggle-btn{
            width:40px;
            height:40px;
            font-size:18px;
            border:none;
            border-radius:50%;
            cursor:pointer;
            display:flex;
            align-items:center;
            justify-content:center;
        }

.join{
background:#007bff;
color:white;
}

.pending{
background:orange;
color:white;
}

.joined{
background:green;
color:white;
}

.logout{
background:red;
color:white;
text-decoration: none;
}

.section{
display:flex;
flex-wrap:wrap;
gap:12px;
align-items: stretch;   
}


.card3,
.card4{

padding:16px;
border-radius:8px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);

flex:0 1 280px;
max-width:280px;
min-height:120px;

display:flex;
flex-direction:column;
justify-content:center;

}

.card2 button,
.card3 button,
.card4 button{
    margin-top:10px;
}

.completed {
    background:gray;
    color:white;
}

.preview-back-btn{
    background:rgba(255,255,255,0.15);
    color:white;
    padding:6px 12px;
    border-radius:6px;
    text-decoration:none;
    font-size:14px;
    font-weight:500;
    transition:0.2s;
}

.preview-back-btn:hover{
    background:rgba(255,255,255,0.25);
}

button:disabled{
opacity:0.6;
cursor:not-allowed;
}

.footer{
text-align:center;
padding:15px;
margin-top:40px;
font-size:14px;
background:var(--card-color);
color:var(--text-color);
border-top:1px solid rgba(0,0,0,0.1);
}

.header{
width:100%;
box-sizing:border-box;
}

.menu{
display:flex;
gap:20px;
margin-left:20px;
}

.menu a{
text-decoration:none;
color:white;
font-size:20px;
font-weight:500;
}


.menu a{
padding:6px 8px;
border-radius:5px;
transition:0.2s;
}

.menu a:hover{
background:rgba(255,255,255,0.15);
}

html{
scroll-behavior:smooth;
}

.header{
position:sticky;
top:0;
z-index:1000;
} 

#topBtn{
    position:fixed;
    bottom:30px;
    right:30px;
    z-index:1000;
    background:#007bff;
    color:white;
    border:none;
    border-radius:50%;
    width:45px;
    height:45px;
    font-size:30px;
    cursor:pointer;
    box-shadow:0 4px 10px rgba(0,0,0,0.2);
    transition:0.3s;

    opacity:0;
    visibility:hidden;
}

#topBtn.show{
    opacity:1;
    visibility:visible;
}

#topBtn:hover{
    transform:scale(1.1);
    background:#0056b3;
}


body.dark #topBtn{
    background:#444;
}


:root {
    --header-height: 100px;
}

h3 {
    scroll-margin-top: var(--header-height);
}



.menu a.active {
    background: rgba(255,255,255,0.3);
}

@media(max-width:768px){


.header{
    flex-direction:column;
    align-items:flex-start;
    gap:10px;
    padding:12px 15px;
}


.header-left{
    display:flex;
    align-items:center;
    gap:10px;
}


.menu{
    width:100%;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin:8px 0;
}

.menu a{
    font-size:14px;
    padding:6px 8px;
}


.header-right{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    font-size:14px;
}


.logo{
    width:35px;
    height:35px;
}


.portal-title{
    font-size:14px;
}


.container{
    padding:10px;
}


.section{
    display:flex;
    flex-wrap:wrap;
    gap:12px;
}


.section a{
    flex:1 1 100%;
}


.card2,
.card3,
.card4{
    flex:1 1 100%;
    max-width:100%;
    margin:0;
}


.card2 p,
.card3 p,
.card4 p{
    word-wrap:break-word;
    overflow-wrap:break-word;
}
.countdown {
    font-weight: bold;
    color: #ffd700; 
}


.btn{
    width:auto;
    font-size:14px;
    padding:6px 10px;
}


h3{
    margin-top:20px;
}


#topBtn{
    bottom:20px;
    right:20px;
    width:40px;
    height:40px;
    font-size:22px;
}

}

</style>

</head>

<body>

<?php if(isset($_GET['preview'])): ?>


<?php endif; ?>

<div class="header">

<div class="header-left">

<a href="student_dashboard.php">
    <img src="adbu_app_logo_512x512.png" class="logo">
</a>

<div class="portal-title">
Club & Society Management Portal
<br>
<span>Student Dashboard</span>
</div>

</div>

<div class="menu">

<a href="#announcements">Announcements</a>
<a href="#joined-clubs">Joined Clubs</a>
<a href="#available-clubs">Clubs</a>
<a href="#events">Events</a>

</div>

<div class="header-right">

<?php if($preview): ?>
<a href="admin_dashboard.php" class="preview-back-btn">
⬅ Admin
</a>
<?php endif; ?>

<!-- 👤 <?php echo $name; ?> -->

👤 Welcome, <?php echo htmlspecialchars($name); ?>



<button onclick="toggleMode()" class="toggle-btn" id="modeToggle">🌙</button>

<a href="logout.php" class="btn logout">Logout</a>

</div>

</div>


<div class="container">

<!-- <h3>Announcements</h3> -->
<h3 id="announcements">Announcements</h3>

<?php foreach($announcements as $a): ?>

<div class="card1">

<h4><?php echo htmlspecialchars($a['title']); ?></h4>

<!-- <p><?php echo $a['body']; ?></p> -->

<p><?php echo nl2br(htmlspecialchars($a['body'])); ?></p>

<small>
Posted by <?php echo $a['full_name']; ?>
</small>

</div>

<?php endforeach; ?>



<!-- <h3>Joined Clubs</h3> -->
<h3 id="joined-clubs">Joined Clubs</h3>

<?php if(!empty($joined_clubs)): ?>

    <div class="section">

    <?php foreach($joined_clubs as $club): ?>

        <a href="student_club_details.php?club_id=<?php echo $club['club_id']; ?><?php if($preview) echo '&preview=1'; ?>"
            style="text-decoration:none; color:inherit;">

        <div class="card2">

            <h4><?php echo htmlspecialchars($club['club_name']); ?></h4>

            <p><?php echo htmlspecialchars($club['description']); ?></p>

            <button class="btn joined">Joined</button>

        </div>
    </a>

    <?php endforeach; ?>

    </div>

<?php else: ?>

    <p>You have not joined any clubs yet.</p>

<?php endif; ?>

<!-- <h3>Available Clubs</h3> -->
<h3 id="available-clubs">Available Clubs</h3>

<div class="section">

<?php foreach($clubs as $club): ?>

<?php if(isset($club['status']) && $club['status'] == "APPROVED") continue; ?>

<div class="card3">

<h4><?php echo $club['club_name']; ?></h4>

<p><?php echo $club['description']; ?></p>


<?php if($club['status']=="APPROVED"): ?>

<button class="btn joined">Joined</button>

<?php elseif($club['status']=="PENDING"): ?>

<button class="btn pending">Request Pending</button>

<?php elseif($club['status']=="REJECTED"): ?>

<?php
$attempts = isset($club['join_attempts']) ? $club['join_attempts'] : 0;

// cooldown check
$can_reapply = true;

if(isset($club['rejected_at']) && $club['rejected_at']){
    $rejected_time = strtotime($club['rejected_at']);
    $current_time = time();

    $hours = ($current_time - $rejected_time) / 3600;

    if($hours < 48){
        $can_reapply = false;
    }
}
?>

<button class="btn" style="background:red;color:white;" disabled>
Rejected (<?php echo $attempts; ?>/3)
</button>

<?php if($attempts < 3): ?>

    <?php if($can_reapply): ?>

        <form method="POST" action="join_club.php">
            <input type="hidden" name="club_id" value="<?php echo $club['club_id']; ?>">
            <button class="btn join">Reapply</button>
        </form>

    <?php else: ?>

        <button class="btn" style="background:gray;color:white;" disabled>
            Wait for 48 hours
        </button>

    <?php endif; ?>

<?php else: ?>

    <button class="btn" style="background:black;color:white;" disabled>
    Limit Reached
    </button>

<?php endif; ?>

<?php else: ?>


<?php if($preview): ?>

<button class="btn join" disabled>Join Club</button>

<?php else: ?>

<form method="POST" action="join_club.php">
<input type="hidden" name="club_id" value="<?php echo $club['club_id']; ?>">
<button class="btn join">Join Club</button>
</form>

<?php endif; ?>

<?php endif; ?>

</div>

<?php endforeach; ?>

</div>


<!-- <h3>Events</h3> -->
<h3 id="events">Events</h3>

<?php if(!empty($events)): ?>

<div class="section">

<?php foreach($events as $event): ?>

<div class="card4">

<h4><?php echo htmlspecialchars($event['event_name']); ?></h4>

<p><?php echo htmlspecialchars($event['event_description']); ?></p>

<p><b>Date:</b> <?php echo date("d M Y", strtotime($event['event_date'])); ?></p>

<p><b>Location:</b> <?php echo htmlspecialchars($event['event_location']); ?></p>

<?php
$today = date("Y-m-d");
?>

<?php if($event['event_date'] < $today): ?>

    <button class="btn completed" disabled>Event Completed</button>

<?php elseif($event['registration_status'] == 'REGISTERED'): ?>

    <button class="btn joined" disabled>Registration Done</button>

<?php else: ?>

    

<?php if($preview): ?>

<button class="btn join" disabled>Register</button>

<?php else: ?>

<form method="POST" action="join_event.php">
<input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
<button class="btn join">Register</button>
</form>

<?php endif; ?>


<?php endif; ?>


<br>

<?php if($event['club_id'] != NULL): ?>
    <small><b>Club Event</b></small>
<?php else: ?>
    <small><b>Global Event</b></small>
<?php endif; ?>

</div>

<?php endforeach; ?>

</div>

<?php else: ?>

<p>No events available.</p>

<?php endif; ?>



</div>

<footer class="footer">

<p>
© 2026 Don Bosco University | Club & Society Management Portal | Developed by Jasmine & Manish
</p>

</footer>

<script>

function toggleMode(){

    const btn = document.getElementById("modeToggle");

    document.body.classList.toggle("dark");

    if(document.body.classList.contains("dark")){
        localStorage.setItem("mode","dark");
        btn.innerHTML = "☀";   
    } else {
        localStorage.setItem("mode","light");
        btn.innerHTML = "🌙";   
    }
}


// When page loads, it helps in setting correct icon
if(localStorage.getItem("mode") === "dark"){
    document.body.classList.add("dark");
    document.getElementById("modeToggle").innerHTML = "☀";
}

document.addEventListener("DOMContentLoaded", function(){

    const topBtn = document.getElementById("topBtn");

    window.addEventListener("scroll", function(){

        if(document.documentElement.scrollTop > 200){
            topBtn.classList.add("show");
        } else {
            topBtn.classList.remove("show");
        }

    });

});

function scrollToTop(){
    window.scrollTo({
        top:0,
        behavior:"smooth"
    });
}

const sections = document.querySelectorAll("h3");
const menuLinks = document.querySelectorAll(".menu a");

let isClickScrolling = false;

window.addEventListener("scroll", () => {

    if (isClickScrolling) return;

    let current = "";

    sections.forEach(section => {
        const sectionTop = section.offsetTop - 130;
        const sectionHeight = section.offsetHeight;

        if (pageYOffset >= sectionTop) {
            current = section.getAttribute("id");
        }
    });

    
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 50) {
        current = "events";
    }

    menuLinks.forEach(link => {
        link.classList.remove("active");
        if (link.getAttribute("href") === "#" + current) {
            link.classList.add("active");
        }
    });

});


document.querySelectorAll(".menu a").forEach(link => {
    link.addEventListener("click", function(){

        menuLinks.forEach(l => l.classList.remove("active"));
        this.classList.add("active");

        
        isClickScrolling = true;

        setTimeout(() => {
            isClickScrolling = false;
        }, 500); // delay time
    });
});


</script>

<button onclick="scrollToTop()" id="topBtn">⬆</button>
</body>
</html>
