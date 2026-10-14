<?php
// ===============================
// Database configuration
// ===============================
$dbHost = "localhost";
$dbUsername = "root";
$dbPassword = ""; // empty password for local XAMPP
$dbName = "lost_and_found_management_systems";

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Email (SMTP) configuration

$smtpHost = 'smtp.gmail.com';                 // Gmail SMTP server
$smtpUsername = 'mohamadadli0709@gmail.com';       // your Gmail address
$smtpPassword = 'vxlropzphcelipod';     // 16-char App Password from Google
$smtpPort = 587;                              // TLS port
$smtpFromEmail = 'mohamadadli0709@gmail.com';      // sender email (same as above)
$smtpFromName = 'Lost & Found System';        // sender name


// Session handling

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

