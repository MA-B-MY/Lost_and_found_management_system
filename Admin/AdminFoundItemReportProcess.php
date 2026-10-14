<?php
session_start(); 
require_once 'AdminDBConfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $itemName = trim($_POST['itemName']);
    $floorLevel = (int)$_POST['floorLevel'];
    $secretNotes = trim($_POST['secretNotes']);

    $picturePath = null;
    // Fix: Ensure variable name matches throughout the script
    $targetDir = "../uploads/"; 
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (isset($_FILES["itemPicture"]) && $_FILES["itemPicture"]["error"] == 0) {
        $fileExtension = strtolower(pathinfo($_FILES["itemPicture"]["name"], PATHINFO_EXTENSION));
        $newFileName = "item_" . uniqid() . "." . $fileExtension; 
        $targetFilePath = $targetDir . $newFileName; // Fix: Now uses the correct directory path

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedTypes)) {
            if (move_uploaded_file($_FILES["itemPicture"]["tmp_name"], $targetFilePath)) {
                $picturePath = $targetFilePath; 
            } else {
                exit("ERROR: File upload failed.");
            }
        } else {
            exit("ERROR: Invalid file type.");
        }
    } else {
        $picturePath = "No image uploaded.";
    }

    $sql = "INSERT INTO found_items (item_name, floor_level, picture_path, secret_notes) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("siss", $itemName, $floorLevel, $picturePath, $secretNotes);
        
        if ($stmt->execute()) {
            header("Location: AdminFoundItemReport.php?status=success");
            exit();
        } else {
            header("Location: AdminFoundItemReport.php?status=error");
        }
        $stmt->close();
    }
    $conn->close();

} else {
    header("Location: AdminFoundItemReport.php");
    exit();
}
?>