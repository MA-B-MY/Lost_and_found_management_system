<?php
session_start();
include "UserDBConfig.php"; 

if (!isset($_SESSION['username'])) {
    header("Location: LoginUser.php");
    exit();
}

$username = $_SESSION['username'];
$user_data = null;
$message = ""; 
$message_type = ""; // To distinguish between 'success' and 'error'

if (!$conn) {
    header("Location: UserDashboard.php?error=db_fail");
    exit();
}

// 1. Fetch CURRENT User Data to pre-fill the form
$sql = "SELECT id, username, email, user_image_path1, id_number, user_image_path2 
        FROM UserData
        WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$stmt->close();

if (!$user_data) {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: LoginUser.php?error=user_not_found");
    exit();
}

// 2. Handle status messages from UserEditAccountProcess.php
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $message = "Your account has been successfully updated!";
        $message_type = "success";
    } elseif ($_GET['status'] == 'email_exists') {
        $message = "That email is already registered to another account.";
        $message_type = "error";
    } elseif ($_GET['status'] == 'upload_fail') {
        $message = "Profile picture upload failed. Please check file size and type.";
        $message_type = "error";
    } elseif ($_GET['status'] == 'invalid_password') {
        $message = "New password and confirm password do not match.";
        $message_type = "error";
    } elseif ($_GET['status'] == 'error') {
        $message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : "An unknown error occurred.";
        $message_type = "error";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Account </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        :root { --primary-green: #004d40; --sidebar-width: 280px; }
        body { background-color: #f4f6f8; color: #333; }

        /* Header & Sidebar */
        .header { background-color: var(--primary-green); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; }
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: -280px; background: white; transition: 0.3s; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar.active { left: 0; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #333; display: block; border-left: 5px solid transparent; }
        .sidebar a:hover { background: #e0f2f1; color: var(--primary-green); border-left-color: var(--primary-green); }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* Content */
        .main-content { margin-top: 60px; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-green); font-weight: bold; }
        
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 25px; }
        h1 { color: var(--primary-green); margin-bottom: 20px; text-align: center; }
        
        /* Forms */
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; }
        .preview-img { max-width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid var(--primary-green); margin: 10px 0; }
        input[type="submit"] { width: 100%; background: var(--primary-green); color: white; padding: 14px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        
        /* Alert Boxes */
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
        .alert-error { background-color: #ffebee; color: #d32f2f; border-color: #ffcdd2; }

        .warning-box { background: #fff5f5; border-left: 5px solid #d32f2f; padding: 20px; border-radius: 4px; }
        .warning-title { color: #d32f2f; font-weight: bold; margin-bottom: 10px; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title"><strong>Edit Account</strong></div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div style="padding: 20px; font-weight: bold; color: var(--primary-green);">Menu</div>
       
        <a href="UserReportMissingItem.php">Report Missing Item</a>
        <a href="UserMissingItemStatus.php">View Status</a>
        <a href="UserEditAccount.php" style="background:#e0f2f1; border-left-color: var(--primary-green);">Edit Account</a>
        <a href="UserContactUs.php">Contact Us</a>
        <a href="Logout.php" style="color: #d32f2f; margin-top: 20px; border-top: 1px solid #eee;">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="UserDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="container">
            <?php if ($message): ?>
                <div class="alert <?php echo ($message_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
                    <strong><?php echo ($message_type == 'success') ? 'Success!' : 'Error:'; ?></strong> 
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <h1>Edit Profile</h1>
                <form action="UserEditAccountProcess.php" method="POST" enctype="multipart/form-data">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>

                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>

                    <label for="new_password">New Password (leave blank to keep current)</label>
                    <input type="password" id="new_password" name="new_password">
                    
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password">

                    <label>Profile Picture</label>
                    <?php if (!empty($user_data['user_image_path1'])): ?>
                        <img src="<?php echo htmlspecialchars($user_data['user_image_path1']); ?>" alt="Profile" class="preview-img">
                    <?php else: ?>
                        <div style="width:100px; height:100px; background:#ddd; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:10px;">No Image</div>
                    <?php endif; ?>
                    <input type="file" name="profile_picture" accept="image/*">

                    <input type="submit" value="Save All Changes" style="margin-top: 20px;">
                </form>
            </div>

            <div class="warning-box">
                <div class="warning-title">⚠️ RESTRICTED INFORMATION</div>
                <p style="font-size: 0.9em; color: #666; margin-bottom: 15px;">
                    Identity verification data (ID Numbers and ID Photos) can only be changed by visiting the Admin office.
                </p>
                <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #eee;">
                    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($user_data['id_number']); ?></p>
                    <p style="margin-top: 10px;"><strong>Verified ID Copy:</strong></p>
                    <?php if (!empty($user_data['user_image_path2'])): ?>
                        <img src="<?php echo htmlspecialchars($user_data['user_image_path2']); ?>" style="max-width: 150px; margin-top: 5px; border: 1px solid #ccc;">
                    <?php else: ?>
                        <small>No ID document on file.</small>
                    <?php endif; ?>
                </div>
            </div>
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