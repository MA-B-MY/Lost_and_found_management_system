<?php
session_start();
include "AdminDBConfig.php";

if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['type'])) {
    $id = intval($_GET['id']);
    $type = $_GET['type'];

    // Determine which table to delete from
    if ($type === 'registered') {
        $sql = "DELETE FROM founditemreport WHERE resolution_id = ?";
    } elseif ($type === 'guest') {
        $sql = "DELETE FROM founditemreportfornonuser WHERE resolution_id = ?";
    } else {
        die("Invalid request type.");
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo "<script>alert('Record deleted successfully'); window.location.href='AdminHistory.php';</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt->close();
} else {
    header("Location: AdminHistory.php");
}
$conn->close();
?>