<?php
session_start();
require_once 'AdminDBConfig.php';

// 1. Session Security Check
if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

$user = null;
$message = "";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $message = "Invalid user ID.";
} else {
    $user_id = (int)$_GET['id'];
    $table_name = "userdata";

    if (isset($conn)) {
        $sql = "SELECT * FROM " . $table_name . " WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
            } else {
                $message = "User not found.";
            }
            $stmt->close();
        } else {
            $message = "Database query preparation failed: " . $conn->error;
        }
    } else {
        $message = "Database connection failed.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Full User View</title>
    <style>
        /* --- SHARED LAYOUT STYLES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root {
            --primary-blue: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-width: 280px;
            --light-blue-bg: #e3f2fd;
        }
        body { background-color: #f4f6f8; color: #333; }

        /* Header & Sidebar */
        .header { background-color: var(--primary-blue); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; }
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #333; display: block; border-left: 5px solid transparent; }
        .sidebar a:hover, .active-nav { background-color: var(--light-blue-bg); color: var(--primary-blue); border-left: 5px solid var(--primary-blue); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* Content Card */
        .main-content { margin-top: 60px; padding: 20px; }
        .content-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        
        /* Grid Layout for Details */
        .details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .info-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .info-item strong { color: #555; font-size: 0.9rem; text-transform: uppercase; display: block; }
        .info-item span { font-size: 1.1rem; font-weight: 500; color: var(--primary-dark); }

        /* Images */
        .image-section { margin-top: 30px; border-top: 2px solid var(--light-blue-bg); padding-top: 20px; }
        .image-gallery { display: flex; gap: 20px; flex-wrap: wrap; margin-top: 15px; }
        .img-box { text-align: center; background: #fafafa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; }
        .img-box img { max-width: 350px; border-radius: 4px; display: block; margin-bottom: 10px; }
        
        /* Buttons */
        .action-btns { margin-top: 30px; display: flex; gap: 15px; }
        .btn { padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: 600; }
        .edit-btn { background: var(--primary-blue); color: white; }
        .delete-btn { background: #d32f2f; color: white; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Admin Dashboard - User Details</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div style="padding: 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold;">Admin Menu</div>

        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php" class="active-nav">View User Accounts</a>
        <a href=" AdminHistory.php"> View History</a>
        <a href="AdminContactUS.php" >Contact Us</a>
        
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <div class="content-card">
            <a href="ViewUserAccount.php" class="back-link">← Back to User List</a>

            <?php if (!empty($message)): ?>
                <p style="color: #d32f2f; background: #ffebee; padding: 15px; border-radius: 4px;"><?php echo htmlspecialchars($message); ?></p>
            <?php elseif ($user): ?>
                <h1 style="color: var(--primary-blue); margin-bottom: 5px;">Profile: <?php echo htmlspecialchars($user['username']); ?></h1>
                <p style="color: #666; margin-bottom: 20px;">System ID: #<?php echo htmlspecialchars($user['id']); ?></p>

                <div class="details-grid">
                    <div class="info-item"><strong>Email Address</strong><span><?php echo htmlspecialchars($user['email']); ?></span></div>
                    <div class="info-item"><strong>Phone Number</strong><span><?php echo htmlspecialchars($user['phoneUser']); ?></span></div>
                    <div class="info-item"><strong>ID / Matrix Number</strong><span><?php echo htmlspecialchars($user['id_number']); ?></span></div>
                    <div class="info-item"><strong>Account Type</strong><span><?php echo ($user['choice'] == 1) ? 'Student' : 'Staff'; ?></span></div>
                    <div class="info-item"><strong>Registration Date</strong><span><?php echo htmlspecialchars($user['created_at']); ?></span></div>
                </div>

                <div class="image-section">
                    <h3>Identification Images</h3>
                    <div class="image-gallery">
                        <?php if (!empty($user['user_image_path1'])): ?>
                        <div class="img-box">
                            <img src="<?php echo htmlspecialchars($user['user_image_path1']); ?>" alt="Image 1">
                            <small>Primary Photo</small>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($user['user_image_path2'])): ?>
                        <div class="img-box">
                            <img src="<?php echo htmlspecialchars($user['user_image_path2']); ?>" alt="Image 2">
                            <small>Secondary Photo</small>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if (empty($user['user_image_path1']) && empty($user['user_image_path2'])): ?>
                        <p style="color: #888; margin-top: 10px;">No images uploaded for this user.</p>
                    <?php endif; ?>
                </div>

                <div class="action-btns">
                    <a href="EditUserAccount.php?id=<?php echo $user['id']; ?>" class="btn edit-btn">Modify Account</a>
                    <a href="DeleteUserAccount.php?id=<?php echo $user['id']; ?>" class="btn delete-btn" 
                       onclick="return confirm('Delete user <?php echo $user['username']; ?> permanently?');">Delete User</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function toggleMenu() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>

</body>
</html>
<?php include '../footer.html'; ?>