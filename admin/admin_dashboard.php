<?php
// session_start();
require_once "session_config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Restrict access - Admins only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "ADMIN") {
    header("Location: login_page.html");
    exit();
}

// ===== DATABASE CONNECTION =====
$host   = "localhost";
$dbname = "club_portal";
$user   = "root";
$pass   = "NITISH77";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ===== HANDLE FORM SUBMISSIONS =====
$successMessage = '';
$errorMessage   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

   // ---- CREATE CLUB ----
if ($action === 'create_club') {

    $club_name      = trim($_POST['club_name'] ?? '');
    $club_category  = trim($_POST['club_category'] ?? '');
    $club_desc      = trim($_POST['club_description'] ?? '');
    $club_president = trim($_POST['club_president'] ?? '');
    $club_max       = intval($_POST['club_max_members'] ?? 0);

    $created_by = $_SESSION['user_id'];
    $founder_id = $_SESSION['user_id']; 

    if (empty($club_name) || empty($club_category) || empty($club_desc)) {

        $errorMessage = 'club';

    } else {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO clubs
                (club_name, category, description, president, max_members, created_by, founder_id, created_at)
                VALUES
                (:club_name, :category, :description, :president, :max_members, :created_by, :founder_id, NOW())
            ");

            $stmt->execute([
                ':club_name'  => $club_name,
                ':category'   => $club_category,
                ':description'=> $club_desc,
                ':president'  => $club_president,
                ':max_members'=> $club_max > 0 ? $club_max : NULL,
                ':created_by' => $created_by,
                ':founder_id' => $founder_id
            ]);

            $_SESSION['successMessage'] = 'club_created';
            header("Location: admin_dashboard.php");
            exit();

        } catch (PDOException $e) {

    if ($e->errorInfo[1] == 1062) {
        $_SESSION['errorMessage'] = 'duplicate_club';
    } else {
        $_SESSION['errorMessage'] = 'club';
    }

    header("Location: admin_dashboard.php");
    exit();
}
    }
}

// Event Creation 

if ($action === 'create_event') {

    $event_type = $_POST['event_type'] ?? '';
    $event_name = trim($_POST['event_name'] ?? '');
    $event_desc = trim($_POST['event_description'] ?? '');
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $event_location = trim($_POST['event_location'] ?? '');

    $club_id = NULL;

    if ($event_type === 'club') {
        $club_id = intval($_POST['event_club_id'] ?? 0);
        if ($club_id <= 0) {
            $_SESSION['errorMessage'] = 'event';
            header("Location: admin_dashboard.php");
            exit();
        }
    }

    if (
        empty($event_type) ||
        empty($event_name) ||
        empty($event_desc) ||
        empty($event_date) ||
        empty($event_time) ||
        empty($event_location)
    ) {
        $_SESSION['errorMessage'] = 'event';
        header("Location: admin_dashboard.php");
        exit();
    }

    $stmt = $pdo->prepare("
        INSERT INTO events
        (club_id, event_name, event_description, event_date, event_time, event_location, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $club_id,
        $event_name,
        $event_desc,
        $event_date,
        $event_time,
        $event_location,
        $_SESSION['user_id']
    ]);

    $_SESSION['successMessage'] = 'event_created';
    header("Location: admin_dashboard.php");
    exit();
}


    // ---- POST ANNOUNCEMENT ----
    if ($action === 'post_announcement') {

    $ann_title  = trim($_POST['ann_title'] ?? '');
    $ann_body   = trim($_POST['ann_body'] ?? '');
    $ann_type   = $_POST['ann_type'] ?? '';
    $ann_date   = !empty($_POST['ann_date']) ? $_POST['ann_date'] : date('Y-m-d');
    $ann_priority = trim($_POST['ann_priority'] ?? 'normal');
    $posted_by  = $_SESSION['user_id'];

    $club_id = NULL;

    if ($ann_type === "club") {
        $club_id = intval($_POST['club_id'] ?? 0);
        if ($club_id <= 0) {
            $errorMessage = 'announcement';
        }
    }

    if (empty($ann_title) || empty($ann_body) || empty($ann_type) || $errorMessage === 'announcement') {
        $errorMessage = 'announcement';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO announcements 
                (club_id, title, body, announcement_date, priority, posted_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $club_id,
                $ann_title,
                $ann_body,
                $ann_date,
                $ann_priority,
                $posted_by
            ]);

            $_SESSION['successMessage'] = 'announcement_posted';
            header("Location: admin_dashboard.php");
            exit();

        } catch (PDOException $e) {
            $errorMessage = 'announcement';
        }
    }
}

}


// ===== READ FLASH MESSAGES =====
$successMessage = $_SESSION['successMessage'] ?? '';
$errorMessage   = $_SESSION['errorMessage'] ?? '';

unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);


$name = $_SESSION['full_name'];



$stmt = $pdo->query("
SELECT announcement_id, title 
FROM announcements 
ORDER BY created_at DESC
");
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);


// FETCH ALL CLUBS
try {
    // $stmt = $pdo->query("SELECT club_name, category, president, created_at FROM clubs ORDER BY club_id DESC");
    $stmt = $pdo->query("
SELECT club_id, club_name, category, president, created_at 
FROM clubs 
ORDER BY club_id DESC
");
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $clubs = [];
}

$stmt = $pdo->query("
SELECT event_id, event_name, event_date 
FROM events 
ORDER BY event_date DESC
");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="theme.css">
    <meta charset="UTF-8">
    <title>Admin Dashboard | Club and Society Management Portal </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="Assam_Don_Bosco_University_Logo.jpg">

    <style>
        :root {
        --header-height: 80px;
        } 

        
        
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
         font-family: 'Segoe UI', Tahoma, sans-serif;
         background: var(--bg-color);
         color: var(--text-color);
         min-height:100vh;
         display:flex;
         flex-direction:column;
        }

        body,
        .header,
        .welcome-card,
        .feature-card,
        .form-section{
            transition: background 0.3s ease, color 0.3s ease;
        }

        .header {
            position: sticky;
            top: 0;
            height: var(--header-height);
            z-index: 1000;

            color: white;
            background: var(--header-color);

            padding: 16px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;

            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            backdrop-filter: blur(10px);
        }


        .header-right{
            display:flex;
            gap:12px;
            align-items:center;
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

        .logo-link {
            display: inline-block;
            cursor: pointer;
        }

        .logo-link:hover {
            transform: scale(1.05);
            transition: 0.2s;
        }

        .portal-title{
        font-size:16px;
        font-weight:600;
        line-height:1.2;
        }

        .portal-title span{
        font-size:12px;
        opacity:0.8;
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

        .header h2 { font-weight: 500; }
        .logout-btn {
            background-color: #ff4d4d; border: none;
            padding: 8px 14px; color: white; border-radius: 5px; cursor: pointer;
        }
        .logout-btn:hover { background-color: #cc0000; }

        
        .preview-btn{
            background: rgba(255,255,255,0.15);
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: 0.2s;
        }

        .preview-btn:hover{
            background: rgba(255,255,255,0.30);
        }

        .menu-bar {
            position: sticky;
            top: var(--header-height);
            z-index: 999;
            

            display: flex;
            justify-content: center;
            gap: 30px;
            padding: 12px;

            background: var(--card-color);
            border-bottom: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);

            backdrop-filter: blur(12px);

        }

        .menu-bar a {
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
            transition: 0.2s;
        }

        
        .menu-bar a:hover {
            color: #007bff;
            transform: translateY(-1px);
        }

        html {
            scroll-behavior: smooth;
        }

        
        .container {
            padding: 40px;
            flex: 1;
        }

        .welcome-card {
            padding: 25px; border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08); margin-bottom: 30px;
            background: var(--card-color);
            color: var(--text-color);
        }

        section, .welcome-card {
            scroll-margin-top: calc(var(--header-height) + 60px);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;

            align-items: stretch; 
        }

        .feature-card {
            /* background: white; */
            background: var(--card-color);
            color: var(--text-color);
            border: 1px solid rgba(255,255,255,0.05);
            /* border-left: 4px solid transparent; */

            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            cursor: pointer;
            border-left: 4px solid transparent;

            min-height: 130px;              
            display: flex;                 
            flex-direction: column;         
            justify-content: center;        
        }

        body.dark .feature-card {
            background: #141414;   
            border: 1px solid rgba(255,255,255,0.06);
        }

        body.dark .feature-card h4 {
            color: #ffffff;
        }

        body.dark .feature-card p {
            color: #bfbfbf; 
        }

        body.dark .feature-card:hover {
            background: #1f1f1f;
            transform: translateY(-6px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.7);
        }

        body.dark .feature-card.active {
            background: #222222;
            border-left-color: #007bff;
        }

        .feature-card:hover { transform: translateY(-5px); border-left-color: #007bff; }
        .feature-card.active { border-left-color: #007bff; background-color: #f0f6ff; }
        .feature-card h4 { margin-bottom: 10px; color: var(--text-color); }
        .feature-card p { font-size: 14px;  color: var(--text-color);  }

        .form-section {
            padding: 30px; border-radius: 10px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.08); margin-bottom: 30px;
            display: none; animation: fadeIn 0.3s ease;
            background: var(--card-color);
            color: var(--text-color);
        }
        body.dark .form-section h3 {
            color: #ffffff;
        }

        body.dark .form-section h3 span {
            color: #4da3ff;
        }

        .form-section.visible { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        .form-section h3 {
            margin-bottom: 20px; color: #0f2027; font-size: 18px;
            border-bottom: 2px solid #007bff; padding-bottom: 10px;
        }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #333; margin-bottom: 6px; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 10px 14px; border: 1px solid #ccc;
            border-radius: 6px; font-size: 14px; font-family: inherit;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: #007bff; outline: none; box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        .btn-submit {
            background-color: #007bff; color: white; border: none;
            padding: 11px 24px; border-radius: 6px; font-size: 15px; cursor: pointer;
        }
        .btn-submit:hover { background-color: #0056b3; }
        .btn-cancel {
            background-color: #e0e0e0; color: #333; border: none;
            padding: 11px 20px; border-radius: 6px; font-size: 15px; cursor: pointer; margin-left: 10px;
        }
        .btn-cancel:hover { background-color: #bbb; }

        .message { padding: 12px 18px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; display: none; }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error   { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .error-text { color: #dc3545; font-size: 12px; margin-top: 4px; display: none; }



        .action-row {
             display: flex;
             justify-content: space-between;
             margin-top: 15px;
        }

        .view-link {
             color: #007bff;
             font-weight: bold;
            text-decoration: none;
        }

        .delete-link {
         color: red;
        font-weight: bold;
        text-decoration: none;
        }

        #backToTop {
            position: fixed;
            bottom: 30px;
            right: 30px;

            background: #007bff;
            color: white;

            border: none;

            width: 45px;        
            height: 45px;       

            border-radius: 50%;
            font-size: 26px;    

            cursor: pointer;

            box-shadow: 0 4px 10px rgba(0,0,0,0.2);

            z-index: 1000;

            opacity: 0;
            visibility: hidden;

            transition: 0.3s;
        }

        #backToTop:hover {
            transform: scale(1.1);
            background: #0056b3;
        }

        #backToTop.show {
            opacity: 1;
            visibility: visible;
        }

        

        .footer{
            width:100%;
            position:relative;
            left:0;
            bottom:0;
            text-align:center;
            padding:18px;
            background:var(--card-color);
            border-top:1px solid rgba(0,0,0,0.1);
            margin-top:auto;
        }

    
    
        @media(max-width:768px){

    .header{
        flex-direction:row;   
        justify-content:space-between;
        align-items:center;
        padding:12px 15px;   
    }

    .header h2{
        font-size:18px;      
    }

    .header-right{
        gap:8px;           
    }

    .toggle-btn{
        width:35px;
        height:35px;
        font-size:16px;
    }

    .logout-btn{
        padding:6px 10px;
        font-size:13px;
    }

}
</style>
</head>
<body>

<div class="header">

    <div class="header-left">

    <a href="admin_dashboard.php" class="logo-link">
        <img src="adbu_app_logo_512x512.png" class="logo" alt="University Logo">
    </a>

    <div class="portal-title">
            Club & Society Management Portal
            <br>
            <span>Admin Dashboard</span>
        </div>

    </div>

    <div class="header-right">

    <a href="student_dashboard.php?preview=1" class="preview-btn">
    👁 Student View
    </a>

    <button onclick="toggleMode()" class="toggle-btn">🌙</button>

    <form method="POST" action="logout.php" style="margin:0;">
        <button type="submit" class="logout-btn">Logout</button>
    </form>

    </div>

</div>

<div class="menu-bar">

    <a href="#manage-section">Quick Actions</a>
    <a href="#clubs-section">Clubs</a>
    <a href="#events-section">Events</a>
    <a href="#announcements-section">Announcements</a>

</div>

<div class="container">

    <div class="welcome-card">
        <h3>Hi <?php echo htmlspecialchars($name); ?> 👋</h3>
        <p>Manage clubs, events, memberships, and users from here.</p>
    </div>



    <div class="welcome-card" id="manage-section">

    <h3>⚡ Quick Actions</h3>
    <br>

    <div class="features">
        <div class="feature-card" id="card-create-club" onclick="toggleForm('create-club')">
            <h4>➕ Create Club/Society</h4>
            <p>Add new clubs to the system.</p>
        </div>
        <a href="manage_memberships.php" style="text-decoration:none;color:inherit;">
<div class="feature-card">

    <h4>📋 Manage Membership Requests</h4>

    <p>Approve or reject student join requests.</p>

</div>
</a>
        <div class="feature-card" id="card-create-event" onclick="toggleForm('create-event')">
            <h4>📅 Create Event</h4>
            <p>Schedule new events for clubs or globally.</p>
        </div>
        <div class="feature-card" id="card-announcement" onclick="toggleForm('announcement')">
            <h4>📢 Post Announcements</h4>
            <p>Share important updates with club members.</p>
        </div>
        <a href="view_users.php" style="text-decoration:none;color:inherit;">
        <div class="feature-card">
            <h4>👥 View All Users</h4>
            <p>Monitor registered students and admins.</p>
        </div>
        </a>
        <a href="participation_reports.php" style="text-decoration:none;color:inherit;">
<div class="feature-card">
    <h4>📊 Participation Reports</h4>
    <p>Analyze event participation records.</p>
</div>
</a>
    </div>
    


    <!-- CREATE CLUB FORM -->
    <div class="form-section" id="form-create-club">
        <h3>➕ Create New Club/Society</h3>

        <div class="message success" id="club-success">✅ Club created successfully!</div>
        <div class="message error" id="club-error"> ⚠️ Please fix the errors below. </div>
        <div class="message error" id="club-duplicate" style="display:none;"> ⚠️ Club name already exists.</div>

        <form method="POST" action="admin_dashboard.php" onsubmit="return validateClubForm()">
            <input type="hidden" name="action" value="create_club">
            <div class="form-row">
                <div class="form-group">
                    <label>Club Name *</label>
                    <input type="text" id="club_name" name="club_name"
                           value="<?php echo htmlspecialchars($_POST['club_name'] ?? ''); ?>"
                           placeholder="e.g. Photography Club">
                    <span class="error-text" id="club_name_error">Club name is required.</span>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select id="club_category" name="club_category">
                        <option value="">-- Select Category --</option>
                        <?php foreach (['arts'=>'Arts & Culture','sports'=>'Sports','tech'=>'Technology','academic'=>'Academic','social'=>'Social & Community'] as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo (($_POST['club_category'] ?? '') === $val) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="error-text" id="club_category_error">Please select a category.</span>
                </div>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea id="club_description" name="club_description" placeholder="Describe the club..."><?php echo htmlspecialchars($_POST['club_description'] ?? ''); ?></textarea>
                <span class="error-text" id="club_description_error">Description is required.</span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Club President / Lead</label>
                    <input type="text" name="club_president"
                           value="<?php echo htmlspecialchars($_POST['club_president'] ?? ''); ?>"
                           placeholder="Full name">
                </div>
                <div class="form-group">
                    <label>Max Members</label>
                    <input type="number" name="club_max_members"
                           value="<?php echo htmlspecialchars($_POST['club_max_members'] ?? ''); ?>"
                           placeholder="e.g. 50" min="1">
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Club</button>
            <button type="button" class="btn-cancel" onclick="toggleForm('create-club')">Cancel</button>
        </form>
    </div>


    <!-- Create Event form  -->
    <div class="form-section" id="form-create-event">
    <h3>📅 Create Event</h3>

    <div class="message success" id="event-success">
        ✅ Event created successfully!
    </div>

    <div class="message error" id="event-error">
        ⚠️ Please fix the errors below.
    </div>

    <form method="POST" action="admin_dashboard.php">
        <input type="hidden" name="action" value="create_event">

        <div class="form-group">
            <label>Event Type *</label>
            <select name="event_type" id="event_type" onchange="toggleEventClub()" required>
                <option value="">-- Select Type --</option>
                <option value="global">Global Event</option>
                <option value="club">Club Specific</option>
            </select>
        </div>

        <div class="form-group" id="event_club_group" style="display:none;">
            <label>Select Club *</label>
            <select name="event_club_id">
                <option value="">-- Select Club --</option>
                <?php foreach($clubs as $club): ?>
                    <option value="<?php echo $club['club_id']; ?>">
                        <?php echo htmlspecialchars($club['club_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Event Name *</label>
            <input type="text" name="event_name" required>
        </div>

        <div class="form-group">
            <label>Description *</label>
            <textarea name="event_description" required></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="event_date" required>
            </div>
            <div class="form-group">
                <label>Time *</label>
                <input type="time" name="event_time" required>
            </div>
        </div>

        <div class="form-group">
            <label>Location *</label>
            <input type="text" name="event_location" required>
        </div>

        <button type="submit" class="btn-submit">Create Event</button>
        <button type="button" class="btn-cancel" onclick="toggleForm('create-event')">Cancel</button>
    </form>
</div>

    <!-- POST ANNOUNCEMENT FORM -->
    <div class="form-section" id="form-announcement">
        <h3>📢 Post Announcement</h3>
        <div class="message success" id="ann-success">✅ Announcement posted successfully!</div>
        <div class="message error"   id="ann-error">⚠️ Please fix the errors below.</div>
        <form method="POST" action="admin_dashboard.php" onsubmit="return validateAnnouncementForm()">
            <input type="hidden" name="action" value="post_announcement">
            <div class="form-row">
                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" id="ann_title" name="ann_title"
                           value="<?php echo htmlspecialchars($_POST['ann_title'] ?? ''); ?>"
                           placeholder="e.g. Club Registration Open">
                    <span class="error-text" id="ann_title_error">Title is required.</span>
                </div>
                <div class="form-group">
                    <label>Announcement Type *</label>
                        <select id="ann_type" name="ann_type" onchange="toggleClubSelect()">
                            <option value="">-- Select Type --</option>
                            <option value="global">Global (All Students)</option>
                            <option value="club">Specific Club</option>
                        </select>
                    <span class="error-text" id="ann_type_error">Please select type.</span>
                </div>
            </div>
                    <div class="form-group" id="club_select_group" style="display:none;">
                        <label>Select Club *</label>
                        <select name="club_id">
                            <option value="">-- Select Club --</option>
                            <?php foreach ($clubs as $club): ?>
                                <option value="<?php echo $club['club_id']; ?>">
                                    <?php echo htmlspecialchars($club['club_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
            <div class="form-group">
                <label>Announcement Body *</label>
                <textarea id="ann_body" name="ann_body" placeholder="Write your announcement here..."><?php echo htmlspecialchars($_POST['ann_body'] ?? ''); ?></textarea>
                <span class="error-text" id="ann_body_error">Body is required.</span>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="ann_date"
                           value="<?php echo htmlspecialchars($_POST['ann_date'] ?? date('Y-m-d')); ?>">
                </div>
                <div class="form-group">
                    <label>Priority</label>
                    <select name="ann_priority">
                        <?php foreach (['normal'=>'Normal','important'=>'Important','urgent'=>'Urgent'] as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo (($_POST['ann_priority'] ?? 'normal') === $val) ? 'selected' : ''; ?>>
                                <?php echo $label; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-submit">Post Announcement</button>
            <button type="button" class="btn-cancel" onclick="toggleForm('announcement')">Cancel</button>
        </form>
    </div>

</div>

 <div class="welcome-card" id="clubs-section">
<h3>🏆 All Clubs</h3>
<br>

<div class="features">

<?php foreach ($clubs as $club): ?>

<div class="feature-card">

    <h4>🏆 <?php echo htmlspecialchars($club['club_name']); ?></h4>

    <p>Category: <?php echo htmlspecialchars($club['category']); ?></p>

    <p>President: <?php echo htmlspecialchars($club['president']); ?></p>

    <br>

    <div class="action-row">

    <a href="club_details.php?club_id=<?php echo $club['club_id']; ?>" 
       class="view-link">
        🔍 View
    </a>

    <a href="delete_club.php?club_id=<?php echo $club['club_id']; ?>" 
       onclick="return confirm('Are you sure you want to delete this club?');"
       class="delete-link">
        🗑 Delete
    </a>

</div>

</div>


<?php endforeach; ?>

</div>



    </div>

<div class="welcome-card" id="events-section">

<h3>📅 All Events</h3>
<br>

<div class="features">

<?php foreach ($events as $event): ?>

<div class="feature-card">

<h4><?php echo htmlspecialchars($event['event_name']); ?></h4>

<p>Date: <?php echo date("d M Y", strtotime($event['event_date'])); ?></p>

<br>

<div class="action-row">

    <a href="admin_event_details.php?event_id=<?php echo $event['event_id']; ?>"
       class="view-link">
       🔍 View
    </a>

    <a href="delete_event.php?event_id=<?php echo $event['event_id']; ?>"
       onclick="return confirm('Delete this event?');"
       class="delete-link">
       🗑 Delete
    </a>

</div>

</div>

<?php endforeach; ?>

</div>

</div>




<div class="welcome-card" id="announcements-section">

<h3>📢 All Announcements</h3>
<br>

<div class="features">

<?php foreach ($announcements as $ann): ?>

<div class="feature-card">

<h4><?php echo htmlspecialchars($ann['title']); ?></h4>

<br>

<div class="action-row">

    <a href="announcement_details.php?id=<?php echo $ann['announcement_id']; ?>"
       class="view-link">
       🔍 View
    </a>

    <a href="delete_announcement.php?id=<?php echo $ann['announcement_id']; ?>"
       onclick="return confirm('Delete this announcement?');"
       class="delete-link">
       🗑 Delete
    </a>

</div>

</div>

<?php endforeach; ?>

</div>

</div>

</div>



<button id="backToTop">⬆</button>

<footer class="footer">
<p>© <?php echo date("Y"); ?> Don Bosco University | Club & Society Management Portal | Developed by Jasmine & Manish</p>
</footer>

<script>
    // Auto-open form and show message from PHP result
    const successMessage = "<?php echo $successMessage; ?>";
    const errorMessage   = "<?php echo $errorMessage; ?>";

   window.addEventListener('DOMContentLoaded', () => {

    if (successMessage === 'club_created') {
        toggleForm('create-club');
        showMsg('club-success', 'success');
    } 
    else if (errorMessage === 'club') {
    toggleForm('create-club');
    showMsg('club-error', 'error');
    }
    else if (errorMessage === 'duplicate_club') {
    toggleForm('create-club');
    showMsg('club-duplicate', 'error');
    }
    
    else if (successMessage === 'event_created') {
    toggleForm('create-event');
    showMsg('event-success', 'success');
    }
    else if (errorMessage === 'event') {
    toggleForm('create-event');
    showMsg('event-error', 'error');
    }
    else if (successMessage === 'announcement_posted') {
        toggleForm('announcement');
        showMsg('ann-success', 'success');
    } 
    else if (errorMessage === 'announcement') {
        toggleForm('announcement');
        showMsg('ann-error', 'error');
    }


});


    function toggleForm(type) {
        const formMap = { 
    'create-club': 'form-create-club', 
    'create-event': 'form-create-event',
    'announcement': 'form-announcement' 
};

const cardMap = { 
    'create-club': 'card-create-club', 
    'create-event': 'card-create-event',
    'announcement': 'card-announcement' 
};
        const form = document.getElementById(formMap[type]);
        const card = document.getElementById(cardMap[type]);
        const isVisible = form.classList.contains('visible');

        document.querySelectorAll('.form-section').forEach(f => f.classList.remove('visible'));
        document.querySelectorAll('.feature-card').forEach(c => c.classList.remove('active'));

        if (!isVisible) {
            form.classList.add('visible');
            card.classList.add('active');
            const yOffset = -120; 
            const y = form.getBoundingClientRect().top + window.pageYOffset + yOffset;

            window.scrollTo({ top: y, behavior: 'smooth' });
        }
    }

    function validateClubForm() {
        let valid = true;
        ['club_name_error','club_category_error','club_description_error'].forEach(id => document.getElementById(id).style.display = 'none');
        document.querySelectorAll('#form-create-club .message').forEach(m => m.style.display = 'none');

        if (!document.getElementById('club_name').value.trim())        { document.getElementById('club_name_error').style.display = 'block'; valid = false; }
        if (!document.getElementById('club_category').value)           { document.getElementById('club_category_error').style.display = 'block'; valid = false; }
        if (!document.getElementById('club_description').value.trim()) { document.getElementById('club_description_error').style.display = 'block'; valid = false; }

        if (!valid) showMsg('club-error', 'error');
        return valid;
    }

    function validateAnnouncementForm() {

    let valid = true;

    ['ann_title_error','ann_type_error','ann_body_error']
        .forEach(id => document.getElementById(id).style.display = 'none');

    document.querySelectorAll('#form-announcement .message')
        .forEach(m => m.style.display = 'none');

    if (!document.getElementById('ann_title').value.trim()) {
        document.getElementById('ann_title_error').style.display = 'block';
        valid = false;
    }

    if (!document.getElementById('ann_type').value) {
        document.getElementById('ann_type_error').style.display = 'block';
        valid = false;
    }

    if (!document.getElementById('ann_body').value.trim()) {
        document.getElementById('ann_body_error').style.display = 'block';
        valid = false;
    }

    if (!valid) showMsg('ann-error', 'error');

    return valid;
}

    function showMsg(id, type) {
        const el = document.getElementById(id);
        el.className = 'message ' + type;
        el.style.display = 'block';
    }

    function toggleMode(){

    document.body.classList.toggle("dark");

    const btn = document.querySelector(".toggle-btn");

    if(document.body.classList.contains("dark")){
        localStorage.setItem("mode","dark");
        btn.innerHTML="☀";
    }else{
        localStorage.setItem("mode","light");
        btn.innerHTML="🌙";
    }
}

if(localStorage.getItem("mode")==="dark"){
    document.body.classList.add("dark");
    const btn = document.querySelector(".toggle-btn");
    if(btn) btn.innerHTML="☀";
}

function toggleClubSelect() {
    const type = document.getElementById("ann_type").value;
    const clubGroup = document.getElementById("club_select_group");

    if (type === "club") {
        clubGroup.style.display = "block";
    } else {
        clubGroup.style.display = "none";
    }
}

function toggleEventClub() {
    const type = document.getElementById("event_type").value;
    const clubGroup = document.getElementById("event_club_group");

    if (type === "club") {
        clubGroup.style.display = "block";
    } else {
        clubGroup.style.display = "none";
    }
}

const backToTopBtn = document.getElementById("backToTop");

// Show button when scrolling
window.onscroll = function () {
    if (document.documentElement.scrollTop > 200) {
        backToTopBtn.classList.add("show");
    } else {
        backToTopBtn.classList.remove("show");
    }
};

// Scroll to top
backToTopBtn.onclick = function () {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
};





</script>


</body>
</html>