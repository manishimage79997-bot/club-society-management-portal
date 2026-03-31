<?php
require_once "session_config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only students allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "STUDENT") {
    header("Location: login_page.html");
    exit();
}

// Check club_id
if (!isset($_POST['club_id'])) {
    header("Location: student_dashboard.php");
    exit();
}

$club_id = intval($_POST['club_id']);
$user_id = $_SESSION['user_id'];

// Database connection
$host = "localhost";
$dbname = "club_portal";
$user = "root";
$pass = "";  //Put your sql password here

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 🔍 Check existing record
    $check = $pdo->prepare("
        SELECT * FROM memberships
        WHERE club_id = :club_id AND user_id = :user_id
    ");

    $check->execute([
        ':club_id' => $club_id,
        ':user_id' => $user_id
    ]);

    $row = $check->fetch(PDO::FETCH_ASSOC);

   
    //  IF RECORD EXISTS
    
    if ($row) {

        //  If already approved
        if ($row['status'] == "APPROVED") {
            header("Location: student_dashboard.php?already_joined=1");
            exit();
        }

        //  Max attempts check
        if ($row['join_attempts'] >= 3) {
            header("Location: student_dashboard.php?max_attempts=1");
            exit();
        }

        //  Cooldown check (48 hours)
        if ($row['status'] == "REJECTED" && $row['rejected_at'] != NULL) {

            $rejected_time = strtotime($row['rejected_at']);
            $current_time = time();

            $hours = ($current_time - $rejected_time) / 3600;

            if ($hours < 48) {
                header("Location: student_dashboard.php?cooldown=1");
                exit();
            }
        }

        //  Reapply (update existing row)
        $update = $pdo->prepare("
            UPDATE memberships
            SET status = 'PENDING'
            WHERE club_id = :club_id AND user_id = :user_id
        ");

        $update->execute([
            ':club_id' => $club_id,
            ':user_id' => $user_id
        ]);

        header("Location: student_dashboard.php?reapplied=1");
        exit();
    }

   
    //  FIRST TIME JOIN
   
    else {

        $stmt = $pdo->prepare("
            INSERT INTO memberships (club_id, user_id, status)
            VALUES (:club_id, :user_id, 'PENDING')
        ");

        $stmt->execute([
            ':club_id' => $club_id,
            ':user_id' => $user_id
        ]);

        header("Location: student_dashboard.php?request_sent=1");
        exit();
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
