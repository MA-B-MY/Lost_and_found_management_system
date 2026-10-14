<?php
session_start();
include "UserDBConfig.php";

if (!isset($_SESSION['username'])) {
    header("Location: LoginUser.php");
    exit();
}

$username = $_SESSION['username'];

if (!$conn) {
    header("Location: LoginUser.php?error=db_fail");
    exit();
}

// 1. Fetch User ID
$sql = "SELECT id FROM UserData WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();
$user_id = $user_data['id'];


// 2. Fetch User's OWN Reported Missing Items (Private/Restricted View)
$sql_user_items = "SELECT report_id, item_picture_path, item_name, item_level, date_lost
FROM missingitems
WHERE user_id = ?
ORDER BY reported_at DESC";

$stmt_user_items = $conn->prepare($sql_user_items);
$stmt_user_items->bind_param("i", $user_id);
$stmt_user_items->execute();
$user_items_result = $stmt_user_items->get_result();
$stmt_user_items->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Reports</title>
</head>
<body>
    <h1>Your Reported Items</h1>
    <p><a href="UserDashboard.php">← Back to Dashboard (All Items)</a></p>
    <hr>
    
    <?php if ($user_items_result->num_rows > 0): ?>
    
        <p>You have <?php echo $user_items_result->num_rows; ?> reported missing items:</p>

        <?php while ($row = $user_items_result->fetch_assoc()): ?>
        <div style="border: 1px solid blue; padding: 10px; margin-bottom: 15px;">
            <p><strong>Item Name:</strong> <?php echo htmlspecialchars($row['item_name']); ?></p>
            <p><strong>Lost On:</strong> <?php echo htmlspecialchars($row['date_lost']); ?></p>
            <p><a href="UserMissingItemStatus.php?item_id=<?php echo htmlspecialchars($row['report_id']); ?>">View Details / Status</a></p>
        </div>
        <?php endwhile; ?>
        
    <?php else: ?>
        <div style="border: 2px dashed orange; padding: 30px; background-color: #ffffff; text-align: center;">
            <h3 style="color: #c00; font-size: 1.5em;">You do not have any reported items.</h3>
            <p>Click <a href="UserReportMissingItem.php" style="font-weight: bold;">here</a> to submit your first report.</p>
        </div>
    <?php endif; ?>

</body>
</html>
<?php include '../footer.html'; ?>