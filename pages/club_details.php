<?php
// session_start();
require_once "session_config.php";


// ADMIN only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

$pdo = new PDO("mysql:host=localhost;dbname=club_portal;charset=utf8","root","");  // put your sql password here

// Validate club_id
$club_id = isset($_GET['club_id']) ? intval($_GET['club_id']) : 0;

if ($club_id == 0) {
    echo "Invalid club ID";
    exit();
}

/* CLUB INFO */
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

/* MEMBERS */

$stmt = $pdo->prepare("
SELECT 
u.full_name,
u.email,
u.phone_number,
m.joined_at

FROM memberships m
JOIN users u ON u.user_id = m.user_id

WHERE m.club_id=?
AND m.status='APPROVED'

ORDER BY m.joined_at DESC
");

$stmt->execute([$club_id]);
$members = $stmt->fetchAll();

$total_members = count($members);

?>

<!DOCTYPE html>
<html>
<head>

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="theme.css">
<title>Club Details | Club and Society Management Portal </title>
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

.back-btn{
display:inline-block;
margin-bottom:20px;
padding:10px 18px;
/* background:#0f2027; */
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

table{
width:100%;
border-collapse:collapse;
background:white;
background: var(--card-color);
color: var(--text-color);
}

th, td{
    padding:12px;
    border:1px solid rgba(0,0,0,0.1);
}

th{
background: var(--header-color);
color: white;
}

tr:hover{
background:rgba(0,0,0,0.05);
transition:0.2s;
}

.table-wrapper {
    overflow-x: auto;
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

    table{
        font-size:14px;
    }

    th, td{
        padding:8px;
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

<h2><?php echo htmlspecialchars($club['club_name']); ?></h2>

<p><b>Category:</b> <?php echo htmlspecialchars($club['category']); ?></p>

<p><b>President:</b> <?php echo htmlspecialchars($club['president']); ?></p>

<p><b>Description:</b> <?php echo htmlspecialchars($club['description']); ?></p>

<p><b>Created:</b> <?php echo date(" d M Y ", strtotime($club['created_at'])); ?></p>

<p><b>Total Members:</b> <?php echo $total_members; ?></p>

</div>


<h3>Members List</h3>

<?php if($total_members > 0): ?>

<div class="table-wrapper">
<table>

<tr>
<th>Name</th>
<th>Email</th>
<th>phone Number </th>
<th>Joined At</th>
</tr>

<?php foreach($members as $m): ?>

<tr>

<td><?php echo htmlspecialchars($m['full_name']); ?></td>

<td><?php echo htmlspecialchars($m['email']); ?></td>

<td><?php echo htmlspecialchars($m['phone_number']); ?></td>

<td><?php echo date(" d M Y ", strtotime($m['joined_at'])); ?></td>

</tr>

<?php endforeach; ?>

</table>
</div>

<?php else: ?>

<div class="card">
No members yet
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
