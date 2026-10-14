<?php

session_start(); 
require_once 'EmailConfig.php';

// Initialize variables
$message = "";
$archived_users = [];
$table_name = "deleted_userdata";

// --- Message Handling (from Restore action) ---
$display_message = isset($_GET['msg']) ? htmlspecialchars(urldecode($_GET['msg'])) : null;
$status_type = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : 'info';

// --- 1. Database Query ---
if (isset($conn)) {
    // JOIN d (deleted_userdata) with a (admindata)
    $sql = "SELECT d.*, a.AdminName 
            FROM $table_name d
            LEFT JOIN admindata a ON d.deleted_by_admin_id = a.AdminID 
            ORDER BY d.deleted_at DESC";

    if ($result = $conn->query($sql)) {
        if ($result->num_rows > 0) {
            $archived_users = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            if (is_null($display_message)) {
                $message = "No archived user accounts found.";
            }
        }
    } else {
        $message = "Error fetching archived users: " . $conn->error;
    }
    $conn->close();
} else {
    $message = "Database connection failed (Check AdminDBConfig.php).";
}

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
    <title>View Archived Accounts</title>
    <style>
        /* --- Shared Layout Styles --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root {
            --primary-blue: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-width: 280px;
            --light-blue-bg: #e3f2fd;
            --success-green: #2e7d32;
            --success-hover: #1b5e20;
            --danger-red: #c62828;
        }
        body { background-color: #f4f6f8; color: #333; }

        .header { background-color: var(--primary-blue); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; padding: 5px; }
        .header-title { font-size: 1.2rem; font-weight: 500; }

        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: var(--light-blue-bg); color: var(--primary-blue); border-left: 5px solid var(--primary-blue); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; margin-bottom: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* --- Page Content Styles --- */
        .main-content { margin-top: 60px; padding: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        .back-link:hover { text-decoration: underline; }

        .data-card {
            background: white; padding: 30px; width: 100%; max-width: 1200px;
            margin: 0 auto; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .page-header { border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 25px; }
        h1 { color: var(--primary-blue); font-size: 1.8rem; margin: 0; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        thead tr { background-color: #f8f9fa; border-bottom: 2px solid #e9ecef; }
        th { text-align: left; padding: 12px 15px; font-weight: 600; color: #495057; }
        td { padding: 12px 15px; border-bottom: 1px solid #e9ecef; color: #333; vertical-align: middle; }
        tbody tr:hover { background-color: #f1f3f5; }

        .restore-btn, .delete-btn {
    display: inline-block;
    padding: 6px 12px;
    margin: 4px 0;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    color: white;
    text-align: center;
    width: 80px; /* Ensures consistent width */
}

.restore-btn {
    background-color: #28a745; /* Green */
}

.delete-btn {
    background-color: #dc3545; /* Red */
}

.restore-btn:hover { background-color: #218838; }
.delete-btn:hover { background-color: #c82333; }

        .restore-btn:hover { background-color: var(--success-hover); }
.delete-btn:hover { background-color: var(--success-hover); }
        .alert-box { padding: 15px; margin-bottom: 20px; border-radius: 4px; font-size: 0.95rem; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-error { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .no-data { text-align: center; padding: 40px; color: #777; font-style: italic; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Deleted User</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php">View User Accounts</a>
        <a href="AdminHistory.php">View History</a>
        <a href="AdminContactUS.php">Contact Us</a>
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="ViewUserAccount.php" class="back-link">← Back to Active User List</a>

        <div class="data-card">
            <div class="page-header">
                <h1>Archived User Accounts</h1>
            </div>

            <?php if (!is_null($display_message)): ?>
                <div class="alert-box <?php echo ($status_type == 'success') ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $display_message; ?>
                </div>
            <?php endif; ?>

            <div class="table-responsive">
                <?php if (!empty($archived_users)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Original Created At</th>
                            <th>Deleted At</th>
                            
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archived_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ($user['choice'] == 1) ? 'Student' : 'Staff'; ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($user['deleted_at']); ?></td>
                            
                            <td>
                                <a href="RestoreAccount.php?id=<?php echo $user['id']; ?>" class="restore-btn" 
                                   onclick="return confirm('Restore account <?php echo $user['username']; ?>?');">
                                   Restore
                                </a>
                                <br> <a href="DeleteAccount.php?id=<?php echo $user['id']; ?>" class="delete-btn" 
       onclick="return confirm('Permanently delete account <?php echo $user['username']; ?>? This cannot be undone.');">
       Delete
    </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="no-data">No archived accounts found.</div>
                <?php endif; ?>
            </div>
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