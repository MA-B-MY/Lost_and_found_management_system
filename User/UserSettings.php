<?php
session_start();
include "UserDBConfig.php"; 


if (!isset($_SESSION['username'])) {
    header("Location: LoginUser.php");
    exit();
}

$username = $_SESSION['username'];


// Data Check Improvement (Recommended)
if (!$conn) {
    // Handle database connection error if needed
    header("Location: LoginUser.php?error=db_fail");
    exit();
}

$sql = "SELECT email, phoneUser, choice, id_number, user_image_path1, user_image_path2 
         FROM UserData 
         WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc(); 

$stmt->close();
$conn->close();

// Optional Check: If user was deleted after login
if (!$user_data) {
    session_unset();
    session_destroy();
    header("Location: LoginUser.php?error=user_deleted");
    exit();
}

?>

<!DOCTYPE html> 
<html> 
<head> 
    <title>Dashboard</title> 
</head> 
<body> 
    <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

    <p><a href="Logout.php">Logout</a></p>
<p><a href="UserReportMissingItem.php">Report missing item</a></p>

    <h2>User Information</h2>

    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user_data['phoneUser']); ?></p>
    
    <p><strong>Status:</strong> 
        <?php 
            if ($user_data['choice'] == 1) {
                echo 'Unikl Student';
            } elseif ($user_data['choice'] == 2) {
                echo 'Staff';
            } else {
                echo 'N/A';
            }
        ?>
    </p>
    
    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($user_data['id_number']); ?></p>

    <h3>Images:</h3>

    <?php if (!empty($user_data['user_image_path1'])): ?>
        <p>Image 1:</p>
        <img src="<?php echo htmlspecialchars($user_data['user_image_path1']); ?>" alt="User Image 1" style="max-width:200px; border: 1px solid #ccc;">
    <?php endif; ?>

    <?php if (!empty($user_data['user_image_path2'])): ?>
        <p>Image 2:</p>
        <img src="<?php echo htmlspecialchars($user_data['user_image_path2']); ?>" alt="User Image 2" style="max-width:200px; border: 1px solid #ccc;">
    <?php endif; ?>
    
</body> 
</html>
<?php include '../footer.html'; ?>