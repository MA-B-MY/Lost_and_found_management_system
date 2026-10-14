<?php
require_once 'AdminDBConfig.php';

$user_id = null;
$user = null;
$message = "";
$table_name = "userdata";

// --- 1. Get User ID ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = (int)$_GET['id'];
} elseif (isset($_POST['id']) && is_numeric($_POST['id'])) {
    $user_id = (int)$_POST['id'];
} else {
    $message = "Invalid or missing User ID.";
}

if ($user_id && isset($conn)) {

    // --- 2. Handle Form Submission ---
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $phoneUser = trim($_POST['phoneUser'] ?? '');
        $id_number = trim($_POST['id_number'] ?? '');
        $choice = (int)($_POST['choice'] ?? 0);

        // Fetch current paths to retain them if no new file is uploaded
        $sql_paths = "SELECT user_image_path1, user_image_path2 FROM userdata WHERE id = ?";
        $stmt_p = $conn->prepare($sql_paths);
        $stmt_p->bind_param("i", $user_id);
        $stmt_p->execute();
        $res_p = $stmt_p->get_result()->fetch_assoc();
        $path1 = $res_p['user_image_path1'];
        $path2 = $res_p['user_image_path2'];

        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

        // Handle Profile Picture (Path 1)
        if (!empty($_FILES['user_image_path1']['name'])) {
            $file1 = "profile_" . $user_id . "_" . time() . "." . pathinfo($_FILES['user_image_path1']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['user_image_path1']['tmp_name'], $target_dir . $file1)) {
                $path1 = $target_dir . $file1;
            }
        }

        // Handle ID Picture (Path 2)
        if (!empty($_FILES['user_image_path2']['name'])) {
            $file2 = "id_" . $user_id . "_" . time() . "." . pathinfo($_FILES['user_image_path2']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['user_image_path2']['tmp_name'], $target_dir . $file2)) {
                $path2 = $target_dir . $file2;
            }
        }

        $sql = "UPDATE " . $table_name . " SET email=?, username=?, phoneUser=?, id_number=?, choice=?, user_image_path1=?, user_image_path2=? WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssissi", $email, $username, $phoneUser, $id_number, $choice, $path1, $path2, $user_id);
            if ($stmt->execute()) {
                header("Location: ViewUserAccount.php?status=success");
                exit();
            } else {
                $message = "Error updating record: " . $stmt->error;
            }
        }
    }

    // --- 3. Fetch User Data ---
    $sql_select = "SELECT * FROM " . $table_name . " WHERE id = ?";
    if ($stmt_select = $conn->prepare($sql_select)) {
        $stmt_select->bind_param("i", $user_id);
        $stmt_select->execute();
        $user = $stmt_select->get_result()->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Account</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        :root { --primary-blue: #1565c0; --primary-dark: #0d47a1; --sidebar-width: 280px; }
        body { background-color: #f4f6f8; color: #333; }

        /* Header & Sidebar */
        .header { background-color: var(--primary-blue); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; display: flex; align-items: center; }
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: -280px; background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: #e3f2fd; color: var(--primary-blue); border-left: 5px solid var(--primary-blue); }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* Form Styling */
        .main-content { margin-top: 60px; padding: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 700px; margin: 0 auto; }
        .form-card h1 { color: var(--primary-blue); font-size: 1.8rem; margin-bottom: 25px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        input[type="text"], input[type="email"], select { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        
        /* Image Preview Design */
        .img-container { display: flex; align-items: flex-start; gap: 20px; background: #f9f9f9; padding: 15px; border-radius: 6px; border: 1px dashed #ccc; }
        .img-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 6px; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .file-controls { flex: 1; }
        
        input[type="submit"] { width: 100%; background-color: var(--primary-blue); color: white; padding: 14px; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        input[type="submit"]:hover { background-color: var(--primary-dark); }
        .error-msg { background-color: #ffebee; color: #d32f2f; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ffcdd2; text-align: center; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">User Management</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        <a href="AdminDashboard.php">Dashboard</a><a href="AdminEditAccount.php">Edit Admin Account</a>
        <a href=" AdminViewAllReports.php"> All lost and found report</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php" style="background-color: #e3f2fd; border-left-color: var(--primary-blue);">View User Accounts</a>
          <a href=" AdminHistory.php"> View History</a>
        <a href="AdminContactUS.php" >Contact Us</a>
        
        <a href="AdminLogout.php" style="color: #d32f2f; margin-top: auto; border-top: 1px solid #eee;">Logout</a>
    </nav>


      
    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="ViewUserAccount.php" class="back-link">← Back to User List</a>

        <?php if (!empty($message)): ?>
            <div class="error-msg"><strong>Error:</strong> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($user): ?>
            <div class="form-card">
                <h1>Edit User Account</h1>
                
                <form action="EditUserAccount.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">

                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>User Profile Picture:</label>
                        <div class="img-container">
                            <?php if (!empty($user['user_image_path1'])): ?>
                                <img src="<?php echo htmlspecialchars($user['user_image_path1']); ?>" class="img-preview" alt="Profile">
                            <?php else: ?>
                                <div style="width:120px; height:120px; background:#ddd; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:12px; color:#777;">No Image</div>
                            <?php endif; ?>
                            <div class="file-controls">
                                <p style="font-size: 0.8em; color: #666; margin-bottom: 10px;">Upload new to replace current</p>
                                <input type="file" name="user_image_path1" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Verified ID Document:</label>
                        <div class="img-container">
                            <?php if (!empty($user['user_image_path2'])): ?>
                                <img src="<?php echo htmlspecialchars($user['user_image_path2']); ?>" class="img-preview" alt="ID Card">
                            <?php else: ?>
                                <div style="width:120px; height:120px; background:#ddd; display:flex; align-items:center; justify-content:center; border-radius:6px; font-size:12px; color:#777;">No ID Document</div>
                            <?php endif; ?>
                            <div class="file-controls">
                                <p style="font-size: 0.8em; color: #666; margin-bottom: 10px;">Update ID verification photo</p>
                                <input type="file" name="user_image_path2" accept="image/*">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Phone Number:</label>
                        <input type="text" name="phoneUser" value="<?php echo htmlspecialchars($user['phoneUser']); ?>">
                    </div>

                    <div class="form-group">
                        <label>ID Card Number:</label>
                        <input type="text" name="id_number" value="<?php echo htmlspecialchars($user['id_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label>User Role / Type:</label>
                        <select name="choice">
                            <option value="1" <?php echo ($user['choice'] == 1) ? 'selected' : ''; ?>>Student</option>
                            <option value="0" <?php echo ($user['choice'] != 1) ? 'selected' : ''; ?>>Staff / Faculty</option>
                        </select>
                    </div>

                    <input type="submit" value="Update Account Details">
                </form>
            </div>
        <?php endif; ?>
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