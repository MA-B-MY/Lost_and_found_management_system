<?php
// Include the database configuration
require_once 'AdminDBConfig.php';
session_start();
if (!isset($_SESSION['AdminID'])) {
    header("Location: AdminLogin.php");
    exit();
}
// Initialize variables
$message = "";
$users = [];

// --- VALIDATION AND MESSAGE HANDLING ---
// Check for status messages passed via URL parameters after a redirect (Edit/Delete)
$display_message = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : null;
$status_type = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : 'info';
// --- END MESSAGE HANDLING ---


// Define the table name
$table_name = "userdata";

// --- 1. Database Query ---
if (isset($conn)) {
    // Select all columns from the userdata table
    $sql = "SELECT id, email, username, phoneUser, id_number, choice, user_image_path1, user_image_path2, created_at FROM " . $table_name;

    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            // Fetch all records
            $users = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            // Only set message if no previous message was set from the redirect
            if (is_null($display_message)) {
                $message = "No user accounts found.";
            }
        }
    } else {
        $message = "Error fetching users: " . $conn->error;
    }
    $conn->close();
} else {
    $message = "Database connection failed (Check AdminDBConfig.php).";
}

// If database error occurred, ensure it overrides the display message
if (!empty($message)) {
    $display_message = $message;
    $status_type = 'error';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View User Accounts</title>
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

        /* Fixed Header */
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

        /* --- 2. TABLE & PAGE STYLES --- */
        
        .page-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-header-bar h1 {
            color: var(--primary-blue);
            margin: 0;
            font-size: 1.8rem;
        }

        .btn-secondary {
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 8px 15px;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
            transition: 0.2s;
            font-size: 0.95rem;
        }
        .btn-secondary:hover { background-color: #eee; }

        /* Responsive Table Wrapper */
        .table-wrapper {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow-x: auto; /* Scroll on mobile */
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px; /* Ensure table doesn't squish too much */
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #fafafa;
            font-weight: 600;
            color: #444;
            border-bottom: 2px solid #ddd;
        }

        tr:hover { background-color: #f9f9f9; }

        /* Links in table */
        .clickable a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .clickable a:hover { text-decoration: underline; }

        /* Action Buttons */
        .action-links { display: flex; gap: 8px; }

        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .edit-btn { background-color: #ffca28; color: #333; }
        .edit-btn:hover { background-color: #ffc107; }

        .delete-btn { background-color: #ef5350; color: white; }
        .delete-btn:hover { background-color: #e53935; }

        /* Alerts */
        .alert-box { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 4px; 
            font-weight: 500;
            border: 1px solid transparent;
        }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
        .alert-error { background-color: #ffebee; color: #c62828; border-color: #ffcdd2; }

    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">View User Account</div>
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
        <a href="AdminContactUS.php">Contact Us</a>
        
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="AdminDashboard.php" style="text-decoration: none; color: #007bff;">← Back to Dashboard</a>
        <div class="page-header-bar">
            <h1>User Account Management</h1>
            
            <a href="ViewArchivedAccounts.php" class="btn-secondary">🗑️ View Deleted Accounts</a>
        </div>

        <?php if (!is_null($display_message)): ?>
            <?php 
                $alert_class = ($status_type == 'success') ? 'alert-success' : 'alert-error';
            ?>
            <div class="alert-box <?php echo $alert_class; ?>">
                <?php echo $display_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($users)): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Phone</th>
                        <th>ID Number</th>
                        <th>User Type</th>
                        <th>Created At</th>
                        <th style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        
                        <td class="clickable">
                            <a href="FullViewUserAccount.php?id=<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </a>
                        </td>
                        
                        <td class="clickable">
                            <a href="FullViewUserAccount.php?id=<?php echo $user['id']; ?>">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </a>
                        </td>

                        <td><?php echo htmlspecialchars($user['phoneUser']); ?></td>
                        <td><?php echo htmlspecialchars($user['id_number']); ?></td>
                        
                        <td>
                            <?php 
                                $user_type = ($user['choice'] == 1) ? 'Student' : 'Staff';
                                echo '<span style="background: #f0f0f0; padding: 2px 6px; border-radius: 4px; font-size: 0.9em;">' . htmlspecialchars($user_type) . '</span>';
                            ?>
                        </td>
                        
                        <td style="font-size: 0.9em; color: #666;"><?php echo htmlspecialchars($user['created_at']); ?></td>
                        
                        <td>
                            <div class="action-links">
                                <a href="EditUserAccount.php?id=<?php echo $user['id']; ?>" class="btn-action edit-btn">Edit</a>
                                
                                <a href="DeleteUserAccount.php?id=<?php echo $user['id']; ?>" class="btn-action delete-btn" 
                                   onclick="return confirm('Are you sure you want to delete account <?php echo $user['username']; ?>?');">Delete</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <p>No user accounts found.</p>
        <?php endif; ?>

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