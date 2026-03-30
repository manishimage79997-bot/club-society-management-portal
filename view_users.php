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
$pass = "";  //Put your sql password here

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all users
    $stmt = $pdo->query("
        SELECT user_id, full_name, email, role, created_at
        FROM users
        ORDER BY created_at DESC
    ");

    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="theme.css">
<title>View All Users | Club and Society Management Portal </title>
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

.table-wrapper{
overflow-x:auto;
margin-top:20px;
}

table{
width:100%;
border-collapse:collapse;
background: var(--card-color);
color: var(--text-color);
}

th, td{
padding:12px;
/* border:1px solid #ddd; */
border:1px solid rgba(0,0,0,0.1);
text-align:left;
}

th{
background: var(--header-color);
color:white;
}

tr:hover{
background:rgba(0,0,0,0.05);
transition:0.2s;
}

.footer{
text-align:center;
padding:18px;
font-size:14px;
background:var(--card-color);
color:var(--text-color);
border-top:1px solid rgba(0,0,0,0.1);
}

.admin{
color:green;
font-weight:bold;
}

.student{
color:#007bff;
font-weight:bold;
}

@media(max-width:768px){

body{
padding:15px;
}

th, td{
padding:8px;
font-size:14px;
}

}

.role{
padding:4px 10px;
border-radius:12px;
font-size:12px;
color:white;
}

.role.admin{ background:green; }
.role.student{ background:#007bff; }

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

<a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
<h2>All Registered Users</h2>

<div class="table-wrapper">
<table>

<tr>
<th>User ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Created At</th>
</tr>

<?php foreach($users as $user): ?>

<tr>

<td><?php echo $user['user_id']; ?></td>

<td><?php echo htmlspecialchars($user['full_name']); ?></td>

<td><?php echo htmlspecialchars($user['email']); ?></td>

<td>
<span class="role <?php echo strtolower($user['role']); ?>">
<?php echo htmlspecialchars($user['role']); ?>
</span>
</td>

<td><?php echo date("d M Y", strtotime($user['created_at'])); ?></td>

</tr>

<?php endforeach; ?>

</table>
</div>
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
