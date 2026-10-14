<?php
session_start();
include 'UserDBConfig.php'; 

// 1. Check if the form was actually submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Get the username from the session
    if (!isset($_SESSION['username'])) {
        $_SESSION['error_message'] = "Please log in first.";
        header("Location: Login.php");
        exit();
    }
    $username = $_SESSION['username'];

    // 3. Fetch the ID from the database using the username
    $user_id = null;
    $user_query = "SELECT id FROM userdata WHERE username = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("s", $username);
    $user_stmt->execute();
    $result = $user_stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user_id = $row['id']; 
    } else {
        die("User not found in database.");
    }
    $user_stmt->close();

    // 4. Collect and sanitize form data (CRUCIAL SECTION)
    $item_name   = trim($_POST['item_name']);
    $item_level  = (int)$_POST['level']; 
    $date_lost   = trim($_POST['date_lost']);
    $description = trim($_POST['description']);
    $picture_type = trim($_POST['picture_type']);
    $item_picture_path = NULL; 

    // 5. Handle File Upload
    if (isset($_FILES['item_picture']) && $_FILES['item_picture']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/"; 


if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["item_picture"]["name"], PATHINFO_EXTENSION);
        $new_file_name = uniqid("item_", true) . "." . $file_extension;
        $target_file = $target_dir . $new_file_name;

        if (move_uploaded_file($_FILES["item_picture"]["tmp_name"], $target_file)) {
            $item_picture_path = $target_file; 
        }
    }

    // 6. Create the INSERT query 
    $sql = "INSERT INTO missingitems (user_id, item_name, item_level, date_lost, description, picture_type, item_picture_path)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        // Types: i (user_id), s, i, s, s, s, s
        $stmt->bind_param("isissss", 
            $user_id,
            $item_name,
            $item_level, 
            $date_lost,
            $description,
            $picture_type, 
            $item_picture_path
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Report submitted successfully!";
            header("Location: UserDashboard.php");
            exit(); 
        } else {
            die("Execute failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Prepare failed: " . $conn->error);
    }

    $conn->close();

} else {
    header("Location: UserReportMissingItem.php");
    exit();
}
?>