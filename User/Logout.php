<?php
// Start the session to access session data
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. Destroy the session on the server
session_destroy();

// 3. Clear the session cookie (optional, but good practice)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Redirect the user back to the login page
header("Location: LoginUser.php?status=logged_out");
exit();
?>