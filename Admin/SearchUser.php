<?php
include "AdminDBConfig.php";
$query = $_GET['query'] ?? '';

// Search by username or email
$sql = "SELECT id, username, email FROM UserData WHERE username LIKE ? OR email LIKE ? LIMIT 5";
$searchTerm = "%$query%";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users); // Return an array of matching users
?>