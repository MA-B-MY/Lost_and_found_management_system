<?php
session_start();
require_once 'AdminDBConfig.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: AdminLostItemReport.php");
    exit();
}

// --- 1. Fix Session/Admin Identity ---
if (!isset($_SESSION['AdminID'])) {
    header("Location: AdminLostItemReport.php?status=error&msg=Session+Expired");
    exit();
}

$admin_db_id = $_SESSION['AdminID'];
$reported_by_name = "Unknown Admin"; 
$reported_by_id = "A0000"; 

// Fetch actual Admin details from DB
$sql_admin = "SELECT AdminName, AdminID FROM admindata WHERE id = ?";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("i", $admin_db_id);
$stmt_admin->execute();
$res_admin = $stmt_admin->get_result();
if ($row_admin = $res_admin->fetch_assoc()) {
    $reported_by_name = $row_admin['AdminName'];
    $reported_by_id = $row_admin['AdminID'];
}
$stmt_admin->close();

// --- 2. Collect and Validate Data ---
$item_name = trim($_POST['item_name'] ?? ''); 
$description = trim($_POST['description'] ?? '');
$floor = (int)($_POST['floor'] ?? 0);
$picture_type = trim($_POST['picture_type'] ?? '');
$report_by_admin = isset($_POST['report_by_admin']);

// If not reported by admin, override with manual input
if (!$report_by_admin) {
    $reported_by_name = trim($_POST['reported_by_name'] ?? '');
    $reported_by_id = trim($_POST['reported_by_id'] ?? '');
}

if (empty($item_name)) {
    header("Location: AdminLostItemReport.php?status=error&msg=Item+Name+is+required");
    exit();
}

// --- 3. Handle File Upload ---
$item_picture_path = null;
if (isset($_FILES["item_picture"]) && $_FILES["item_picture"]["error"] == 0) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_extension = strtolower(pathinfo($_FILES["item_picture"]["name"], PATHINFO_EXTENSION));
    $new_file_name = "item_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
        if (move_uploaded_file($_FILES["item_picture"]["tmp_name"], $target_file)) {
            $item_picture_path = $target_file;
        }
    }
}

// --- 4. Final Insert ---
$sql = "INSERT INTO MissingItemAdminSide (item_name, floor, description, reported_by_name, reported_by_id, item_picture_path, picture_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("sisssss", $item_name, $floor, $description, $reported_by_name, $reported_by_id, $item_picture_path, $picture_type);
    
    if ($stmt->execute()) {
        $msg = urlencode("Lost Item Report created successfully!");
        header("Location: AdminLostItemReport.php?status=success&msg=$msg");
    } else {
        $msg = urlencode("Database Error: " . $stmt->error);
        header("Location: AdminLostItemReport.php?status=error&msg=$msg");
    }
    $stmt->close();
} else {
    $msg = urlencode("SQL Prepare Error: " . $conn->error);
    header("Location: AdminLostItemReport.php?status=error&msg=$msg");
}

$conn->close();
exit();
?>