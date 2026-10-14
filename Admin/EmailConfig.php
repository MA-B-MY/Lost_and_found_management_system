<?php
// Include the database configuration file
require_once 'AdminDBConfig.php';


// Email (SMTP) configuration

$smtpHost = 'smtp.gmail.com';// Gmail SMTP server
$smtpUsername = 'mohamadadli0709@gmail.com';// your Gmail address
$smtpPassword = 'vxlropzphcelipod';// 16-char App Password from Google
$smtpPort = 587; // TLS port
$smtpFromEmail = 'mohamadadli0709@gmail.com'; // sender email (same as above)
$smtpFromName = 'Lost & Found System'; // sender name

// ===============================
// Session handling (if needed by other files that include this)
// ===============================
if (session_status() == PHP_SESSION_NONE) {
 session_start();
}
?>