<?php 
session_start(); 
include "UserDBConfig.php"; 
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    // FIXED: Get the value from the 'email' input
    $email = $_POST['email']; 
    $password = $_POST['password']; 
 
    // FIXED: SQL now searches the 'email' column instead of 'username'
    $sql = "SELECT * FROM UserData WHERE email = ?"; 
    $stmt = $conn->prepare($sql); 
    // FIXED: Bind the email variable
    $stmt->bind_param("s", $email); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 
 
    if ($result->num_rows > 0) { 
        $row = $result->fetch_assoc(); 
 
        if ($row['password_hash'] == $password) { 
            // FIXED: Store email in session, but you might prefer username for display
            $_SESSION['username'] = $row['username']; // Store the username from the DB for the dashboard
 
            header("Location: UserDashboard.php"); 
            exit(); 
        } else { 
            header("Location: LoginUser.php?error=invalid_credentials"); 
            exit(); 
        } 
    } else { 
        header("Location: LoginUser.php?error=invalid_credentials"); 
        exit(); 
    } 

    $stmt->close(); 
    $conn->close();
} 

?>