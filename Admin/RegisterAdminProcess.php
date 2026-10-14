<?php
session_start();
// Include the database configuration file
include "AdminDBConfig.php"; 

// --- Configuration ---
$target_dir = "../uploads/";; // Make sure this directory exists and is writable!

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // If not POST, redirect back to the registration form
    header("Location: RegisterAdmin.php?error=invalid_access");
    exit();
}

// Ensure database connection is established
if (!$conn) {
    header("Location: RegisterAdmin.php?error=db_fail");
    exit();
}

// --- 1. Sanitize and Validate Input ---

// Get form data
$AdminName = trim($_POST['AdminName']);
$AdminEmail = trim($_POST['AdminEmail']);
$AdminID = trim($_POST['AdminID']);
$AdminPassword = $_POST['AdminPassword'];
$AdminPicture = $_FILES['AdminPicture'];

// Basic validation
if (empty($AdminName) || empty($AdminEmail) || empty($AdminID) || empty($AdminPassword)) {
    $conn->close();
    header("Location: RegisterAdmin.php?error=missing_fields");
    exit();
}

// Hash the password for security
$hashed_password = password_hash($AdminPassword, PASSWORD_DEFAULT);
$AdminPicturePath = NULL; // Default to NULL if no upload happens

// --- 2. Handle Picture Upload ---
if ($AdminPicture['error'] == UPLOAD_ERR_OK) {
    $file_info = pathinfo($AdminPicture['name']);
    $file_extension = strtolower($file_info['extension']);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($file_extension, $allowed_extensions)) {
        $conn->close();
        header("Location: RegisterAdmin.php?error=invalid_file_type");
        exit();
    }

    // Create a unique file name (e.g., using admin ID and time)
    $new_file_name = $AdminID . "_" . time() . "." . $file_extension;
    $upload_file = $target_dir . $new_file_name;

    if (move_uploaded_file($AdminPicture['tmp_name'], $upload_file)) {
        $AdminPicturePath = $upload_file;
    } else {
        $conn->close();
        header("Location: RegisterAdmin.php?error=upload_failed");
        exit();
    }
} else {
     // If picture is required, uncomment the next three lines
     /*
     $conn->close();
     header("Location: RegisterAdmin.php?error=picture_required");
     exit();
     */
}


// --- 3. Check for Duplicate Entries (Name, Email, ID Number) ---
$sql_check = "SELECT id FROM AdminData WHERE AdminName = ? OR AdminEmail = ? OR AdminID = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("sss", $AdminName, $AdminEmail, $AdminID);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    $conn->close();
    header("Location: RegisterAdmin.php?error=already_exists");
    exit();
}
$stmt_check->close();


// --- 4. Insert Data into Database ---
$sql_insert = "INSERT INTO AdminData (AdminName, AdminEmail, AdminID, AdminPassword, AdminPicturePath) VALUES (?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);

// Bind the parameters (s = string, for all values)
$stmt_insert->bind_param("sssss", $AdminName, $AdminEmail, $AdminID, $hashed_password, $AdminPicturePath);

if ($stmt_insert->execute()) {
    
    // --- Success ---
    $stmt_insert->close();
    $conn->close();

    // Start a new session for the registered admin (optional, depends on your workflow)
    // $_SESSION['AdminName'] = $AdminName; 

  
    // REDIRECT TO LoginAdmin.php
    header("Location: LoginAdmin.php?status=registration_success");
    exit();
    exit();

} else {
    // --- Failure ---
    $stmt_insert->close();
    $conn->close();
    // Redirect with a generic error
    header("Location: RegisterAdmin.php?error=database_insert_failed");
    exit();
}
?>