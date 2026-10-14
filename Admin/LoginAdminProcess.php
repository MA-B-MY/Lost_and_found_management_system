<?php
session_start();
// Include the shared database configuration file
include "AdminDBconfig.php"; // *** Make sure this is the correct path/filename ***

// --- 1. Check for POST request and database connection ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: LoginAdmin.php?error=invalid_access");
    exit();
}

if (!$conn) {
    header("Location: LoginAdmin.php?error=db_fail");
    exit();
}

// --- 2. Input Retrieval and Validation ---
$login_identifier = trim($_POST['AdminName']); // Can be Username or Email
$AdminPassword = $_POST['AdminPassword'];

if (empty($login_identifier) || empty($AdminPassword)) {
    $conn->close();
    header("Location: LoginAdmin.php?error=missing_fields");
    exit();
}

// --- 3. Prepare Query and Fetch Admin Data ---
// Check if the identifier matches either AdminName OR AdminEmail
$sql = "SELECT AdminName, AdminPassword, id FROM AdminData WHERE AdminName = ? OR AdminEmail = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $login_identifier, $login_identifier);
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();
$conn->close();

// --- 4. Verify Credentials ---

if ($admin_data) {
    // Admin found, now verify the password
    $hashed_password = $admin_data['AdminPassword'];

    if (password_verify($AdminPassword, $hashed_password)) {
        
        // Success: Password is correct. Start the session.
        $_SESSION['AdminLoggedIn'] = true;
        $_SESSION['AdminName'] = $admin_data['AdminName'];
        $_SESSION['AdminID'] = $admin_data['id'];

        // Redirect to the Admin Dashboard
        header("Location: AdminDashboard.php");
        exit();
        
    } else {
        // Failure: Password does not match
        header("Location: LoginAdmin.php?error=invalid_credentials");
        exit();
    }
} else {
    // Failure: No admin found with that username/email
    header("Location: LoginAdmin.php?error=invalid_credentials");
    exit();
}

?>