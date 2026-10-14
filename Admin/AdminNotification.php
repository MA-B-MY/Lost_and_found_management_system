<?php
session_start();
include "AdminDBConfig.php"; // Adjust if your admin connection uses a different file

// 1. Session Security Check for Admin
if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

// --- NEW: Handle Delete Request ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM admin_notifications WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: AdminNotification.php"); // Refresh page
    exit();
}

// 2. Fetch notifications from the database
$sql = "SELECT * FROM admin_notifications ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - User Notifications</title>
    <style>
        /* --- SHARED LAYOUT STYLES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Arial, sans-serif; }
        :root {
            --primary-blue: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-width: 280px;
            --light-blue-bg: #e3f2fd;
        }
        body { background-color: #f4f6f8; color: #333; }

        .header { background-color: var(--primary-blue); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; }
        .header-title { font-size: 1.2rem; font-weight: 500; }

        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; }
        .sidebar a:hover { background-color: var(--light-blue-bg); color: var(--primary-blue); border-left: 5px solid var(--primary-blue); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; margin-bottom: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        .main-content { margin-top: 80px; padding: 20px; max-width: 1000px; margin-left: auto; margin-right: auto; }
        
        /* Notification Styling */
        .notif-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 15px; border-left: 5px solid #f39c12; position: relative; }
        .notif-time { font-size: 0.85rem; color: #888; margin-bottom: 5px; }
        .notif-user { font-weight: bold; color: var(--primary-dark); }
        .notif-msg { margin-top: 5px; font-size: 1.05rem; line-height: 1.4; }
        .empty-state { text-align: center; padding: 50px; color: #777; }

        /* --- NEW: Delete Button Styling --- */
        .delete-btn { position: absolute; top: 20px; right: 20px; color: #d32f2f; text-decoration: none; font-size: 0.9rem; font-weight: bold; }
        .delete-btn:hover { text-decoration: underline; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Admin Dashboard - Notifications</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a><a href=" AdminViewAllReports.php"> All lost and found report</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php">View User Accounts</a>
        <a href="AdminNotification.php" style="background-color: var(--light-blue-bg); border-left-color: var(--primary-blue); color: var(--primary-blue);">User Notifications</a>
        <a href="AdminContactUS.php">Contact Us</a>
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content"> <a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>
        <h1 style="margin-bottom: 20px; color: var(--primary-blue);">User Claim Notifications</h1>

        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="notif-card">
                    <a href="AdminNotification.php?delete_id=<?php echo $row['id']; ?>" class="delete-btn" onclick="return confirm('Delete this notification?')">Delete</a>
                    <div class="notif-time"><?php echo date('F j, Y, g:i a', strtotime($row['created_at'])); ?></div>
                    <div class="notif-user">From: <?php echo htmlspecialchars($row['username']); ?></div>
                    <div class="notif-msg"><?php echo htmlspecialchars($row['message']); ?></div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <h2>No notifications found</h2>
                <p>When users notify you about claiming an item, they will appear here.</p>
            </div>
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
<?php 
$conn->close();
include '../footer.html'; 
?>