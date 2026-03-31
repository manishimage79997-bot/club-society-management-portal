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
$pass = "NITISH77";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);

// HANDLE APPROVE / REJECT
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $membership_id = intval($_POST['membership_id']);
    $action = $_POST['action'];

    if ($action == "approve") {

        $stmt = $pdo->prepare("
            UPDATE memberships
            SET status = 'APPROVED'
            WHERE membership_id = ?
        ");

        $stmt->execute([$membership_id]);

         header("Location: manage_memberships.php");
        exit();
    }

    if ($action == "reject") {

    $stmt = $pdo->prepare("
        UPDATE memberships
        SET status = 'REJECTED',
            rejected_at = NOW(),
            join_attempts = join_attempts + 1
        WHERE membership_id = ?
    ");

    $stmt->execute([$membership_id]);

    header("Location: manage_memberships.php");
    exit();
}
}


// FETCH ALL REQUESTS
$stmt = $pdo->query("
SELECT 
m.membership_id,
u.full_name,
c.club_name,
m.status,
m.joined_at,
m.join_attempts

FROM memberships m

JOIN users u ON m.user_id = u.user_id
JOIN clubs c ON m.club_id = c.club_id

ORDER BY m.joined_at DESC
");

$requests = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>

<link rel="stylesheet" href="theme.css">
<title>Manage Memberships | Club and Society Management Portal </title>
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
        .card{
        /* background: var(--bg-color); */
        background: var(--card-color);
        color: var(--text-color);
        padding:15px;
        margin-bottom:10px;
        border-radius:8px;
        box-shadow:0 4px 10px rgba(0,0,0,0.1);
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
        border:1px solid rgba(0,0,0,0.1);
        text-align:left;
        }

        th{
        background: var(--header-color);
        color:white;
        }

        .btn{
        padding:6px 12px;
        border:none;
        border-radius:5px;
        cursor:pointer;
        margin-right:5px;
        }

        .approve{
        background:green;
        color:white;
        }

        .reject{
        background:red;
        color:white;
        }

        .pending{
        background:orange;
        color:white;
        }

        .approved{
        background:green;
        color:white;
        }

        .rejected{
        background:red;
        color:white;
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

        th, td{
        padding:8px;
        font-size:14px;
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

<a href="admin_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>

<h2>Manage Membership Requests</h2>

<div class="table-wrapper">
<table>

<tr>
<th>Student</th>
<th>Club</th>
<th>Status</th>
<th>Action</th>
</tr>

<?php foreach($requests as $req): ?>

<tr>

<td><?php echo htmlspecialchars($req['full_name']); ?></td>
<td><?php echo htmlspecialchars($req['club_name']); ?></td>
<td>
<?php 
echo htmlspecialchars($req['status']); 

if($req['status'] == "REJECTED"){
    echo " (Attempt: " . $req['join_attempts'] . "/3)";
}
?>
</td>

<td>

<?php if($req['status'] == "PENDING"): ?>

<form method="POST" style="display:inline;">
<input type="hidden" name="membership_id"
value="<?php echo $req['membership_id']; ?>">

<button class="btn approve" name="action" value="approve">Approve</button>
<button class="btn reject" name="action" value="reject">Reject</button>
</form>

<?php elseif($req['status']=="APPROVED"): ?>

<button class="btn approved">Approved</button>

<?php else: ?>

<button class="btn rejected">Rejected</button>

<?php endif; ?>

</td>

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