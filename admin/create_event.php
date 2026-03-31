<?php
// session_start();
require_once "session_config.php";

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

$host = "localhost";
$dbname = "club_portal";
$user = "root";
$pass = "";  //Put your sql password here

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$user,$pass);


// Fetch all clubs for dropdown
$stmt = $pdo->query("SELECT club_id, club_name FROM clubs ORDER BY club_name");
$clubs = $stmt->fetchAll();

$success = "";


if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $name = trim($_POST['event_name'] ?? '');
    $desc = trim($_POST['event_description'] ?? '');
    $date = $_POST['event_date'] ?? '';
    $time = $_POST['event_time'] ?? '';
    $location = trim($_POST['event_location'] ?? '');
    $event_type = $_POST['event_type'] ?? '';

    // Basic validation
    if (empty($name) || empty($desc) || empty($date) || empty($time) || empty($location) || empty($event_type)) {
        die("All fields are required.");
    }

    $club_id = NULL;

    if ($event_type === "club") {

        $club_id = intval($_POST['club_id'] ?? 0);

        // Verify club exists in database
        $check = $pdo->prepare("SELECT club_id FROM clubs WHERE club_id = ?");
        $check->execute([$club_id]);

        if ($check->rowCount() == 0) {
            die("Invalid club selected.");
        }
    }

    $stmt = $pdo->prepare("
        INSERT INTO events
        (club_id, event_name, event_description, event_date, event_time, event_location, created_by)
        VALUES (?,?,?,?,?,?,?)
    ");

    $stmt->execute([
        $club_id,
        $name,
        $desc,
        $date,
        $time,
        $location,
        $_SESSION['user_id']
    ]);

    $success = "Event created successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>


<link rel="stylesheet" href="theme.css">
<title>Create Event</title>

<style>

body{
font-family:Segoe UI;
background:#f4f6f9;
padding:30px;
}

.card{
background:white;
padding:20px;
border-radius:8px;
width:400px;
}

input,textarea{
width:100%;
padding:10px;
margin-bottom:10px;
}

button{
background:#007bff;
color:white;
padding:10px;
border:none;
}

.success{
color:green;
}

</style>

</head>
<body>

<div class="card">

<h2>Create Event</h2>

<?php if($success): ?>
<p class="success"><?php echo $success; ?></p>
<?php endif; ?>

<form method="POST">

<select name="event_type" id="event_type" onchange="toggleEventClub()" required>
    <option value="">-- Select Event Type --</option>
    <option value="global">Global Event</option>
    <option value="club">Club Specific</option>
</select>

<div id="club_select_group" style="display:none;">
    <select name="club_id">
        <option value="">-- Select Club --</option>
        <?php foreach($clubs as $club): ?>
            <option value="<?php echo $club['club_id']; ?>">
                <?php echo htmlspecialchars($club['club_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<input type="text" name="event_name" placeholder="Event Name" required>

<textarea name="event_description" placeholder="Description" required></textarea>

<input type="date" name="event_date" required>

<input type="time" name="event_time" required>

<input type="text" name="event_location" placeholder="Location" required>

<button type="submit">Create Event</button>

</form>

</div>

<script>
function toggleEventClub() {
    const type = document.getElementById("event_type").value;
    const clubGroup = document.getElementById("club_select_group");

    if (type === "club") {
        clubGroup.style.display = "block";
    } else {
        clubGroup.style.display = "none";
    }
}
</script>

</body>
</html>
