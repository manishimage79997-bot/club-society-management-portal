<?php
// session_start();
require_once "session_config.php";


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root",""); //Put your sql password here

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "Invalid Announcement";
    exit();
}

$stmt = $pdo->prepare("
SELECT a.*, u.full_name, c.club_name
FROM announcements a
LEFT JOIN users u ON u.user_id = a.posted_by
LEFT JOIN clubs c ON c.club_id = a.club_id
WHERE a.announcement_id = ?
");

$stmt->execute([$id]);
$announcement = $stmt->fetch();

if (!$announcement) {
    echo "Announcement not found";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>


    <title> Announcement Details | Club and Society Management Portal </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">
    <link rel="stylesheet" href="theme.css">


<style>

:root{
    --bg-color:#f4f6f9;
    --card-color:#ffffff;
    --text-color:#222;
    --primary:#007bff;
}

body.dark{
    --bg-color:#1e1e1e;
    --card-color:#2c2c2c;
    --text-color:#f1f1f1;
}

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


.card{
background: var(--card-color);
color: var(--text-color);
padding:20px;
border-radius:10px;
margin-bottom:20px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
}

h2{
    margin-top:0;
}

hr{
    margin:20px 0;
    border:0;
    border-top:1px solid rgba(0,0,0,0.1);
}

.top-bar{
    margin-bottom:20px;
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


.meta{
    font-size:14px;
    margin-bottom:10px;
    opacity:0.85;
}

.footer{
text-align:center;
padding:18px;
font-size:14px;
background:var(--card-color);
color:var(--text-color);
border-top:1px solid rgba(0,0,0,0.1);
}

@media(max-width:600px){

    body{
        padding:15px;
    }

    .card{
        padding:18px;
    }

    h2{
        font-size:20px;
    }
}

</style>
</head>

<body>

<div class="header">

<div class="header-left">

<img src="adbu_app_logo_512x512.png" class="logo">

<div class="portal-title">
Club & Society Management Portal | Admin Dashboard
</div>

</div>

</div>

<div class="page-container">


<a href="javascript:history.back()" class="back-btn">⬅ Back to Dashboard</a>


<div class="card">

<h2><?php echo htmlspecialchars($announcement['title']); ?></h2>

<div class="meta">
    <p><b>Posted By:</b> <?php echo htmlspecialchars($announcement['full_name']); ?></p>

    <p><b>Club:</b>
    <?php echo $announcement['club_name'] ? htmlspecialchars($announcement['club_name']) : "Global Announcement"; ?>
    </p>

    <p><b>Date:</b> <?php echo date("d M Y", strtotime($announcement['announcement_date'])); ?></p>
</div>

<hr>

<p><?php echo nl2br(htmlspecialchars($announcement['body'])); ?></p>

</div>


</div>


<footer class="footer">
<p>© <?php echo date("Y"); ?> Don Bosco University | Club & Society Management Portal | Developed by Jasmine & Manish</p>
</footer>

<!-- Dark mode automatically synced from dashboard -->
<script>
if(localStorage.getItem("mode")==="dark"){
    document.body.classList.add("dark");
}
</script>

</body>
</html>
