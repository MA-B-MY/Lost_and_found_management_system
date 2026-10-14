<?php
session_start();
require_once 'AdminDBConfig.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Retrieve Data and Setup ---
    $admin_db_id = isset($_POST['admin_db_id']) ? $_POST['admin_db_id'] : null; 
    $admin_name = isset($_POST['admin_name']) ? trim($_POST['admin_name']) : '';
    $admin_email = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($admin_db_id)) {
        header("location: AdminDashboard.php"); 
        exit();
    }
    
    $redirect_url = "AdminEditAccount.php?id=" . urlencode($admin_db_id); 
    $errors = [];
    
    // --- 2. Validation ---
    
    // Check for Admin Name format
    if (empty($admin_name)) {
        $errors[] = "Admin Name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]*$/", $admin_name)) {
        $errors[] = "Only letters/spaces allowed for Admin Name.";
    }

    // Check for Email format
    if (empty($admin_email)) {
        $errors[] = "Admin Email is required.";
    } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Password validation
    if (!empty($new_password)) {
        if (strlen($new_password) < 5 || strlen($new_password) > 10) {
            $errors[] = "Password must be 5-10 characters long.";
        }
        if ($new_password !== $confirm_password) {
            header("location: $redirect_url&status=invalid_password");
            exit();
        }
    }
    
    // --- 3. CONSISTENCY CHECK: Check for Duplicate Admin Name (New Secure Step) ---
    if (empty($errors) && $conn) {
        // Look for any admin with the same AdminName but a different ID (primary key 'id')
        $sql_check_name = "SELECT id FROM admindata WHERE AdminName = ? AND id != ?";
        $stmt_check_name = $conn->prepare($sql_check_name);

        if ($stmt_check_name === false) {
            $conn->close();
            header("location: $redirect_url&status=error&message=" . urlencode("Name check failed to prepare: " . $conn->error));
            exit();
        }

        // 's' for AdminName (string), 'i' for ID (integer)
        $stmt_check_name->bind_param("si", $admin_name, $admin_db_id); 
        $stmt_check_name->execute();
        $result_check_name = $stmt_check_name->get_result();
        
        if ($result_check_name->num_rows > 0) {
            $stmt_check_name->close();
            // Custom redirect status for name conflict
            header("location: $redirect_url&status=name_exists"); 
            exit();
        }
        $stmt_check_name->close();
    }
    
    // --- 4. Database Update (Using Prepared Statements) ---
    if (empty($errors)) {
        
        // --- A. Build SQL dynamically ---
        $sql = "UPDATE admindata SET AdminName = ?, AdminEmail = ?";
        $types = "ss";
        $params = [$admin_name, $admin_email];
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT); 
            $sql .= ", AdminPassword = ?"; 
            $types .= "s";
            $params[] = $hashed_password;
        }
        
        $sql .= " WHERE id = ?"; 
        $types .= "i"; 
        $params[] = $admin_db_id;

        // --- B. Execute Prepared Statement ---
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            $conn->close();
            header("location: $redirect_url&status=error&message=" . urlencode("SQL Update failed to prepare: " . $conn->error));
            exit();
        }
        
        $stmt->bind_param($types, ...$params);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("location: $redirect_url&status=success");
            exit();
        } else {
            $stmt->close();
            $conn->close();
            if ($conn->errno == 1062) { 
                 header("location: $redirect_url&status=email_exists");
                 exit();
            }
            header("location: $redirect_url&status=error&message=" . urlencode($conn->error));
            exit();
        }
    } else {
        // --- 5. Handle General Validation Errors ---
        $error_message = implode(" | ", $errors);
        
        header("location: $redirect_url&status=error&message=" . urlencode("Validation Failed: " . $error_message));
        exit();
    }
} else {
    header("location: AdminDashboard.php");
    exit();
}
// Note: Final $conn->close() block remains removed to prevent the previous fatal error.
?>