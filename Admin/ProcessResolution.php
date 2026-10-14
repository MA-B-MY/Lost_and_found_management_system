<?php
session_start();
include "AdminDBConfig.php";

if (!isset($_SESSION['AdminID'])) {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. VALIDATION
    if (empty($_POST['found_item_id'])) {
        header("Location: AdminFoundOwnerItem.php?error=no_item_selected");
        exit();
    }

    if (!isset($_POST['is_existing_user']) && empty($_POST['owner_name'])) {
        header("Location: AdminFoundOwnerItem.php?error=missing_owner_info");
        exit();
    }

    $found_item_id = $_POST['found_item_id'];
    $floor_level = $_POST['floor_level']; 
    $date_found = $_POST['date_found'];   
    $admin_id = $_SESSION['AdminID'];
    
    $conn->begin_transaction();

    try {
        // --- STEP A: Fetch Found Item Details (Including Picture) ---
        $stmt = $conn->prepare("SELECT item_name, secret_notes, picture_path FROM found_items WHERE id = ?");
        $stmt->bind_param("i", $found_item_id);
        $stmt->execute();
        $item_result = $stmt->get_result();
        $item_data = $item_result->fetch_assoc();

        if (!$item_data) {
            throw new Exception("The found item was not found or already processed.");
        }

        $item_name = $item_data['item_name'];
        $item_picture = $item_data['picture_path']; // Captured for history
        $item_desc = "Floor Level: " . $floor_level; 

        // --- STEP B: Handle Evidence Upload (Saved to ../uploads/) ---
        $targetDir = "../uploads/"; 
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $evidence_path = "";
        $evidence_desc = $_POST['evidence_desc'] ?? "";

        if ($_POST['evidence_choice'] == 'image' && isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] == 0) {
            $evidence_path = $targetDir . "evidence_" . time() . "_" . basename($_FILES['evidence_file']['name']);
            move_uploaded_file($_FILES['evidence_file']['tmp_name'], $evidence_path);
        }

        // --- STEP C: Save to History ---
        if (isset($_POST['is_existing_user']) && !empty($_POST['selected_user_id'])) {
            // CASE 1: REGISTERED USER
            $user_id = $_POST['selected_user_id'];
            
            $sql = "INSERT INTO founditemreport (found_item_id, admin_id, user_id, item_name, item_description, floor_level, evidence_description, evidence_picture_path, date_found, item_picture) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->bind_param("iiisssssss", $found_item_id, $admin_id, $user_id, $item_name, $item_desc, $floor_level, $evidence_desc, $evidence_path, $date_found, $item_picture);
            $stmt_insert->execute();

        } else {
            // CASE 2: NON-USER
            $owner_name = $_POST['owner_name'];
            $owner_sid = $_POST['owner_sid'];
            $owner_email = $_POST['owner_email'];
            $owner_phone = $_POST['owner_phone'];
            
            $id_pic_path = "";
            if (isset($_FILES['owner_id_pic']) && $_FILES['owner_id_pic']['error'] == 0) {
                $id_pic_path = $targetDir . "ids_" . time() . "_" . basename($_FILES['owner_id_pic']['name']);
                move_uploaded_file($_FILES['owner_id_pic']['tmp_name'], $id_pic_path);
            }

            $sql = "INSERT INTO founditemreportfornonuser (found_item_id, admin_id, owner_student_staff_id, owner_name, owner_email, owner_phone, owner_id_picture, item_name, item_description, floor_level, evidence_description, evidence_picture_path, date_found, item_picture) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql);
            $stmt_insert->bind_param("iissssssssssss", $found_item_id, $admin_id, $owner_sid, $owner_name, $owner_email, $owner_phone, $id_pic_path, $item_name, $item_desc, $floor_level, $evidence_desc, $evidence_path, $date_found, $item_picture);
            $stmt_insert->execute();
        }

        // --- STEP D: AUTOMATIC DELETION ---
        $del_found = $conn->prepare("DELETE FROM found_items WHERE id = ?");
        $del_found->bind_param("i", $found_item_id);
        $del_found->execute();

        $del_missing = $conn->prepare("DELETE FROM missingitems WHERE item_name = ?");
        $del_missing->bind_param("s", $item_name);
        $del_missing->execute();

        $del_admin_missing = $conn->prepare("DELETE FROM missingitemadminside WHERE item_name = ?");
        $del_admin_missing->bind_param("s", $item_name);
        $del_admin_missing->execute();

        $conn->commit();
        header("Location: AdminDashboard.php?msg=ResolutionSuccessful");
        exit(); 

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: AdminFoundOwnerItem.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>