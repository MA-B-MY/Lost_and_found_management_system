<?php
session_start();
// Use the correct file name you provided
include "UserDBConfig.php"; 

// --- Configuration ---
$target_dir = "../uploads/"; 

// 2. This line ensures the folder is created in the root if it doesn't exist
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: RegisterUser.php");
    exit();
}

// --- 1. Sanitize and Get Form Data ---
$email = $_POST['emailuser'];
$username = $_POST['username'];
$phone = $_POST['phoneUser'];
$choice = $_POST['choice'];
$id_number = $_POST['id_number'];
$password = $_POST['password']; 
$password_hash = $password; // Using plain text for refresh, but HASH is best practice


// --- 2. Validation Checks (Database Duplicates) ---

// Check 1: Email
$sql_email = "SELECT id FROM UserData WHERE email = ?";
$stmt_email = $conn->prepare($sql_email);
$stmt_email->bind_param("s", $email);
$stmt_email->execute();
$stmt_email->store_result();

if ($stmt_email->num_rows > 0) {
    header("Location: RegisterUser.php?error=email_used");
    exit();
}
$stmt_email->close();

// Check 2: ID Number
$sql_id = "SELECT id FROM UserData WHERE id_number = ?";
$stmt_id = $conn->prepare($sql_id);
$stmt_id->bind_param("s", $id_number);
$stmt_id->execute();
$stmt_id->store_result();

if ($stmt_id->num_rows > 0) {
    header("Location: RegisterUser.php?error=id_used");
    exit();
}
$stmt_id->close();

// Check 3: Phone Number
$sql_phone = "SELECT id FROM UserData WHERE phoneUser = ?";
$stmt_phone = $conn->prepare($sql_phone);
$stmt_phone->bind_param("s", $phone);
$stmt_phone->execute();
$stmt_phone->store_result();

if ($stmt_phone->num_rows > 0) {
    header("Location: RegisterUser.php?error=phone_used");
    exit();
}
$stmt_phone->close();


// --- 3. Handle File Uploads ---
$user_image_path1 = ''; 
$user_image_path2 = ''; 

// Helper function for upload logic
function handle_upload($file_key, $prefix, $target_dir) {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES[$file_key]['tmp_name'];
        $file_extension = pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION);
        $new_file_name = uniqid($prefix, true) . '.' . $file_extension;
        $target_file = $target_dir . $new_file_name;
        
        if (move_uploaded_file($file_tmp_name, $target_file)) {
            return $target_file;
        }
    }
    return '';
}

// Upload the two images
$user_image_path1 = handle_upload('user_image_1', 'user_pic_', $target_dir);
$user_image_path2 = handle_upload('user_image_2', 'student_id_', $target_dir);


// --- 4. Insert Data into Database (If no duplicates found) ---
$sql = "INSERT INTO UserData (email, username, phoneUser, choice, id_number, user_image_path1, user_image_path2, password_hash) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param("sssiisss", 
    $email, 
    $username, 
    $phone, 
    $choice, 
    $id_number, 
    $user_image_path1, 
    $user_image_path2, 
    $password_hash
);

if ($stmt->execute()) {
    // Registration Success: Redirect DIRECTLY to UserLogin.php (no JS)
    header("Location: LoginUser.php");
    exit();
} else {
    // Database insertion failed
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>