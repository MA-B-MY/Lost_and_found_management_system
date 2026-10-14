<?php
session_start();
include "UserDBConfig.php"; 

// --- 1. Security Check ---
if (!isset($_SESSION['username']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: LoginUser.php");
    exit();
}

$current_username = $_SESSION['username'];
$target_dir = "../uploads/"; 
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Fetch current user data
$sql_check = "SELECT id, user_image_path1 FROM UserData WHERE username = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $current_username);
$stmt_check->execute();
$user_data = $stmt_check->get_result()->fetch_assoc();
$stmt_check->close();

if (!$user_data) {
    header("Location: LoginUser.php?error=user_not_found");
    exit();
}

$user_id = $user_data['id'];
$current_profile_picture = $user_data['user_image_path1'];

// --- 2. Input Validation ---
$new_username = trim($_POST['username']);
$new_email = trim($_POST['email']);
$new_password = $_POST['new_password'];
$confirm_password = $_POST['confirm_password'];

if (empty($new_username) || empty($new_email)) {
    header("Location: UserEditAccount.php?status=error&message=Required fields missing");
    exit();
}

// --- 3. Dynamic Query Building ---
$update_columns = [];
$bind_types = "";
$bind_values = [];

// Password Logic (No Hashing + Correct Column Name)
if (!empty($new_password)) {
    if ($new_password !== $confirm_password) {
        header("Location: UserEditAccount.php?status=invalid_password");
        exit();
    }
    $update_columns[] = "password_hash = ?"; // Change to your actual DB column name
    $bind_types .= "s";
    $bind_values[] = $new_password; 
}

// Handle Image Upload
$new_image_path = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
    $file_ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    $new_file_name = $user_id . "_profile_" . time() . "." . $file_ext;
    $upload_file = $target_dir . $new_file_name;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_file)) {
        $new_image_path = $upload_file;
        $update_columns[] = "user_image_path1 = ?";
        $bind_types .= "s";
        $bind_values[] = $new_image_path;
    }
}

// Always update Username and Email
$update_columns[] = "username = ?";
$bind_types .= "s";
$bind_values[] = $new_username;

$update_columns[] = "email = ?";
$bind_types .= "s";
$bind_values[] = $new_email;

// Add User ID for WHERE clause
$bind_types .= "i";
$bind_values[] = $user_id;

// --- 4. Final Execution ---
$sql_update = "UPDATE UserData SET " . implode(', ', $update_columns) . " WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);

if ($stmt_update) {
    $stmt_update->bind_param($bind_types, ...$bind_values);
    if ($stmt_update->execute()) {
        // Delete old image if new one exists
        if ($new_image_path && $current_profile_picture && file_exists($current_profile_picture)) {
            unlink($current_profile_picture);
        }
        $_SESSION['username'] = $new_username;
        header("Location: UserEditAccount.php?status=success");
    } else {
        header("Location: UserEditAccount.php?status=error&message=Execute failed");
    }
    $stmt_update->close();
} else {
    die("SQL Error: " . $conn->error);
}
$conn->close();
?>