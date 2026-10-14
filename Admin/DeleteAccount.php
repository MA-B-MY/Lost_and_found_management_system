<?php
// --- CONFIGURATION INCLUSION ---
require_once 'EmailConfig.php'; 

// --- INITIALIZATION ---
$message = "Unknown error occurred.";
$status = "error"; 
$archive_table = "deleted_userdata";

// --- 1. Get User ID from URL ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "Invalid or missing ID for permanent deletion.";
    header("Location: ViewUserAccount.php?status=" . urlencode($status) . "&msg=" . urlencode($message));
    exit();
}
$user_id = (int)$_GET['id'];

// --- 2. Execution ---
if (isset($conn) && $conn->connect_error === null) {
    try {
        // DELETE from Archive Table permanently
        $sql_delete = "DELETE FROM " . $archive_table . " WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $user_id);
        
        if ($stmt_delete->execute()) {
            if ($stmt_delete->affected_rows > 0) {
                $message = "User account (ID: $user_id) has been permanently deleted.";
                $status = "success";
            } else {
                throw new Exception("User not found or already deleted.");
            }
        } else {
            throw new Exception($stmt_delete->error);
        }
        $stmt_delete->close();

    } catch (Exception $e) {
        $message = "Deletion failed: " . $e->getMessage();
        $status = "error";
    }
    $conn->close();
} else {
    $message = "Database connection failed.";
    $status = "error";
}

// --- 3. Redirect ---
header("Location: ViewUserAccount.php?status=" . urlencode($status) . "&msg=" . urlencode($message));
exit();
?>