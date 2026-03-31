<?php

// Session lifetime = 2 hours (7200 seconds)
$session_lifetime = 7200;

// Extend session lifetime
ini_set('session.gc_maxlifetime', $session_lifetime);
session_set_cookie_params($session_lifetime);

session_start();

// Track user activity
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime)) {
    
    // Session expired
    session_unset();
    session_destroy();
    
    header("Location: login_page.html");
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

?>