<?php
// session_start();
require_once "session_config.php";
session_destroy();
header("Location: login_page.html");
exit();
?>