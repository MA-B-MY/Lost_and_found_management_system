<?php
// --- CONFIGURATION INCLUSION ---
// This file includes AdminDBConfig.php
require_once 'EmailConfig.php'; 

// Note: No PHPMailer setup or 'use' statements are needed.

// --- INITIALIZATION ---
$message = "Unknown error occurred.";
$status = "error"; 

$active_table = "userdata";
$archive_table = "deleted_userdata";
$user_data = null; 

// --- 1. Get User ID from URL ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "Invalid or missing Archive ID for restoration.";
    header("Location: ViewUserAccount.php?status=" . urlencode($status) . "&msg=" . urlencode($message));
    exit();
}
$user_id = (int)$_GET['id'];

// --- 2. Main Transaction Execution ---
if (isset($conn) && $conn->connect_error === null) {
    // Start Transaction
    $conn->begin_transaction();

    try {
        // 2a. SELECT User Record from ARCHIVE table
        // Selecting only the columns needed for the active table
        $sql_select = "SELECT id, email, username, phoneUser, id_number, choice, user_image_path1, user_image_path2, password_hash, created_at FROM " . $archive_table . " WHERE id = ?";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $user_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("User not found in archive table. ID: " . $user_id);
        }
        $user_data = $result->fetch_assoc();
        $stmt_select->close();

        // 2b. INSERT into ACTIVE Table
        $sql_insert = "INSERT INTO " . $active_table . " (id, email, username, phoneUser, id_number, choice, user_image_path1, user_image_path2, password_hash, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($sql_insert);
        
        // Parameter string: i, s*4, i, s*4 -> 'issssissss' (10 parameters)
        $stmt_insert->bind_param(
            "issssissss",
            $user_data['id'], 
            $user_data['email'], 
            $user_data['username'], 
            $user_data['phoneUser'], 
            $user_data['id_number'], 
            $user_data['choice'], 
            $user_data['user_image_path1'], 
            $user_data['user_image_path2'], 
            $user_data['password_hash'], 
            $user_data['created_at'] 
        );
        $stmt_insert->execute();
        $stmt_insert->close();


        // 2c. DELETE from Archive Table
        $sql_delete = "DELETE FROM " . $archive_table . " WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $user_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        
        // Final success. Commit the transaction.
        $conn->commit();
        
        $message = "User account (" . $user_data['username'] . ") successfully restored to active list.";
        $status = "success";

    } catch (Exception $e) {
        // Handle failure
        $conn->rollback();
        $message = "Restoration failed: " . $e->getMessage();
        $status = "error";
    }

    $conn->close();

} else {
    $message = "Database connection failed.";
    $status = "error";
}

// --- 3. Redirect to the User View Page ---
header("Location: ViewUserAccount.php?status=" . urlencode($status) . "&msg=" . urlencode($message));
exit();
?>