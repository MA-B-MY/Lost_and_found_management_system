<?php
session_start();
// Include your database configuration (ensure this path is correct for admin)
include "AdminDBConfig.php"; 

// --- 0. Initial Setup & Session Check (Matching User code pattern) ---
// We check for the primary key (id) set during admin login
if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

// The primary key (id) of the logged-in admin
$admin_db_id = $_SESSION['AdminID']; 
$admin_data = null;
$error_message = ""; // Initialize error/success message variable (Matching User code)

if (!$conn) {
    header("Location: AdminDashboard.php?error=db_fail");
    exit();
}

// 1. Fetch CURRENT Admin Data (Securely using Prepared Statement)
$sql = "SELECT id, AdminID, AdminName, AdminEmail, AdminPicturePath 
        FROM admindata
        WHERE id = ?"; // Lookup by primary key 'id'
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Handle prepare error
    error_log("SQL Prepare Error: " . $conn->error);
    $conn->close();
    header("Location: AdminDashboard.php?fetch_error=db_fail");
    exit();
}

// The bind parameter must be 'i' (integer) since 'id' is INT(11)
$stmt->bind_param("i", $admin_db_id); 
$stmt->execute();
$result = $stmt->get_result();
$admin_data = $result->fetch_assoc();
$stmt->close();


if (!$admin_data) {
    // Admin ID in session not found in DB
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: LoginAdmin.php?error=user_not_found");
    exit();
}

// 2. Check for form submission status (Matching User code pattern)
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $error_message = "The account has been edited successfully."; 
    } elseif ($_GET['status'] == 'email_exists') {
        $error_message = "Error: That email is already registered.";

    } elseif ($_GET['status'] == 'name_exists') {
        $error_message = "Error: That Admin Name is already in use by another administrator.";
// ... rest of the existing code ...
    } elseif ($_GET['status'] == 'upload_fail') {
        $error_message = "Error: Profile picture upload failed.";
    } elseif ($_GET['status'] == 'invalid_password') {
        $error_message = "Error: New password and confirm password do not match.";
    } elseif ($_GET['status'] == 'error' && isset($_GET['message'])) {
        $error_message = htmlspecialchars($_GET['message']);
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Admin Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- 1. SHARED LAYOUT STYLES --- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            /* DARK BLUE Theme */
            --primary-blue: #1565c0; /* Blue 800 */
            --primary-dark: #0d47a1; /* Blue 900 */
            --sidebar-width: 280px;
        }

        body {
            background-color: #f4f6f8;
            color: #333;
        }

        /* Fixed Dark Blue Header */
        .header {
            background-color: var(--primary-blue);
            color: white;
            height: 60px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .menu-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            margin-right: 20px;
            padding: 5px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .menu-btn:hover { background-color: rgba(255,255,255,0.2); }

        .header-title { font-size: 1.2rem; font-weight: 500; }

        /* Slide-out Sidebar */
        .sidebar {
            height: 100vh;
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: calc(var(--sidebar-width) * -1);
            background-color: #ffffff;
            transition: left 0.3s ease-in-out;
            z-index: 1001;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            padding-top: 10px;
        }

        .sidebar.active { left: 0; }

        .sidebar-header {
            padding: 10px 20px 20px 20px;
            font-size: 1.5rem;
            color: var(--primary-blue);
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }

        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 16px;
            color: #333;
            display: block;
            border-left: 5px solid transparent;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background-color: #e3f2fd;
            color: var(--primary-blue);
            border-left: 5px solid var(--primary-blue);
        }

        .logout-link {
            margin-top: auto;
            border-top: 1px solid #eee;
            color: #d32f2f !important;
            margin-bottom: 20px;
        }

        .overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        .overlay.active { display: block; }

        /* Main Content */
        .main-content {
            margin-top: 60px;
            padding: 20px;
        }

        /* --- 2. FORM PAGE STYLES --- */
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: var(--primary-blue);
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }

        /* Card container for the form */
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto; /* Center the card horizontally */
        }

        .form-card h1 {
            color: var(--primary-blue);
            font-size: 1.8rem;
            margin-bottom: 25px;
            text-align: center;
        }

        /* Form Elements */
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        
        /* Readonly input style */
        input[readonly] {
            background-color: #f0f0f0;
            color: #666;
            cursor: not-allowed;
        }

        input[type="file"] {
            margin-bottom: 10px;
        }

        .preview-container {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 4px;
            text-align: center;
            background: #fafafa;
        }
        
        .preview-img {
            max-width: 100px;
            height: auto;
            border-radius: 50%; /* Circle profile pic style */
            border: 2px solid var(--primary-blue);
            margin-top: 5px;
        }

        input[type="submit"] {
            width: 100%;
            background-color: var(--primary-blue);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: var(--primary-dark);
        }

    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Edit Account</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        
        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php" style="background-color: #e3f2fd; border-left-color: var(--primary-blue);">Edit Admin Account</a><a href=" AdminViewAllReports.php"> All lost and found report</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php">View User Accounts</a>
        <a href=" AdminHistory.php"> View History</a>
        <a href="AdminContactUS.php">Contact Us</a>
        
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        
        <a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>

        <?php if ($error_message): ?>
            <div style="background-color: #ffebee; color: #d32f2f; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #ffcdd2; max-width: 600px; margin-left: auto; margin-right: auto;">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <h1>Edit Your Account</h1>
            
            <form action="AdminEditAccountProcess.php" method="POST" enctype="multipart/form-data">
                
                <input type="hidden" name="admin_db_id" value="<?php echo htmlspecialchars($admin_data['id']); ?>">
                
                <label for="admin_id">Admin ID (Card Number):</label>
                <input type="text" id="admin_id" name="admin_id" value="<?php echo htmlspecialchars($admin_data['AdminID']); ?>" readonly>

                <label for="admin_name">Admin Name:</label>
                <input type="text" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_data['AdminName']); ?>" required>

                <label for="admin_email">Email Address:</label>
                <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_data['AdminEmail']); ?>" required>

                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
                
                <label for="new_password">New Password (optional):</label>
                <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password">

                <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

                <label for="profile_picture">Profile Picture:</label>
                <input type="file" id="profile_picture" name="profile_picture">
                
                <div class="preview-container">
                    <span style="font-size: 0.9em; color: #666; display: block; margin-bottom: 5px;">Current Picture:</span>
                    <?php if ($admin_data['AdminPicturePath']): ?>
                        <img src="<?php echo htmlspecialchars($admin_data['AdminPicturePath']); ?>" alt="Current Profile" class="preview-img">
                    <?php else: ?>
                        <p style="font-style: italic; color: #999;">No picture uploaded.</p>
                    <?php endif; ?>
                </div>

                <input type="submit" value="Save Changes">

            </form>
        </div>
    </main>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>

</body>
</html>
<?php include '../footer.html'; ?>