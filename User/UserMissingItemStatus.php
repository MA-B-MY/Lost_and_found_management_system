<?php
session_start();
include "UserDBConfig.php";

if (!isset($_SESSION['username'])) {
    header("Location: LoginUser.php");
    exit();
}

$user_username = $_SESSION['username'];

// Fetch User ID
$stmt_user = $conn->prepare("SELECT id FROM UserData WHERE username = ?");
$stmt_user->bind_param("s", $user_username);
$stmt_user->execute();
$user_id = $stmt_user->get_result()->fetch_assoc()['id'] ?? 0;

$is_detail_mode = isset($_GET['view_id']);

if ($is_detail_mode) {
    // --- MODE: VIEW DETAILS ---
    $report_id = $_GET['view_id'];
    
    // UPDATED SQL: Added report_id, description, item_level, date_lost, picture_type, reported_at
    // For found items, we use placeholders (like 'N/A' or 0) for columns that might not exist in that table
    $sql_details = "
        (SELECT report_id, item_name, description, item_level, date_lost, picture_type, reported_at, item_picture_path, 'Pending' as status 
         FROM missingitems WHERE report_id = ? AND user_id = ?)
        UNION ALL
        (SELECT resolution_id as report_id, item_name, 'Item successfully claimed.' as description, 0 as item_level, DATE(resolved_at) as date_lost, 'N/A' as picture_type, resolved_at as reported_at, evidence_picture_path as item_picture_path, 'Resolved' as status 
         FROM founditemreport WHERE resolution_id = ? AND user_id = ?)";

    $stmt = $conn->prepare($sql_details);
    $stmt->bind_param("iiii", $report_id, $user_id, $report_id, $user_id);
    $stmt->execute();
    $report_details = $stmt->get_result()->fetch_assoc();
} else {
    // --- MODE: LIST ALL ---
    $all_activity = [];
    // Get Active
    $res = $conn->query("SELECT report_id as id, item_name, reported_at as date, 'Pending' as status FROM missingitems WHERE user_id = $user_id");
    while($row = $res->fetch_assoc()) { $all_activity[] = $row; }
    // Get Resolved
    $res = $conn->query("SELECT resolution_id as id, item_name, resolved_at as date, 'Resolved' as status FROM founditemreport WHERE user_id = $user_id");
    while($row = $res->fetch_assoc()) { $all_activity[] = $row; }
}
// --- DELETE LOGIC ---
if (isset($_POST['delete_report']) && isset($_POST['report_id'])) {
    $del_id = $_POST['report_id'];
    // Ensure user can only delete their own PENDING items
    $stmt_del = $conn->prepare("DELETE FROM missingitems WHERE report_id = ? AND user_id = ?");
    $stmt_del->bind_param("ii", $del_id, $user_id);
    
    if ($stmt_del->execute()) {
        header("Location: UserMissingItemStatus.php?deleted=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Report Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- 1. SHARED LAYOUT STYLES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root { --primary-green: #004d40; --sidebar-width: 280px; }
        body { background-color: #f4f6f8; }

        /* Header */
        .header {
            background-color: var(--primary-green); color: white; height: 60px;
            display: flex; align-items: center; padding: 0 20px;
            position: fixed; top: 0; width: 100%; z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .menu-btn {
            background: none; border: none; color: white; font-size: 24px;
            cursor: pointer; margin-right: 20px; padding: 5px;
            display: flex; align-items: center; justify-content: center;
        }
        .header-title { font-size: 1.2rem; font-weight: 500; }

        /* Sidebar */
        .sidebar {
            height: 100vh; width: var(--sidebar-width); position: fixed; top: 0;
            left: calc(var(--sidebar-width) * -1); background-color: #ffffff;
            transition: left 0.3s ease-in-out; z-index: 1001;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px;
        }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px; font-size: 1.5rem; color: var(--primary-green); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: #e0f2f1; color: var(--primary-green); border-left: 5px solid var(--primary-green); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; margin-bottom: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* --- 2. PAGE SPECIFIC STYLES --- */
        .main-content { margin-top: 60px; padding: 20px; }

        /* Back Link Positioned to the far left */
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            margin-left: 10px;
            text-decoration: none;
            color: var(--primary-green);
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }

        /* Centered Container for the Table/Content */
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Table Styling */
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-top: 10px;
        }
        .styled-table thead tr { background-color: var(--primary-green); color: #ffffff; text-align: left; }
        .styled-table th, .styled-table td { padding: 15px 20px; }
        .styled-table tbody tr { border-bottom: 1px solid #dddddd; }
        .styled-table tbody tr:nth-of-type(even) { background-color: #f9f9f9; }
        .styled-table tbody tr:hover { background-color: #f1f1f1; }

        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .status-badge.Pending, .status-badge.Lost { background-color: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
        .status-badge.Resolved, .status-badge.Found { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }

        .btn-view {
            text-decoration: none;
            color: var(--primary-green);
            border: 1px solid var(--primary-green);
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 0.9em;
            transition: all 0.2s;
        }
        .btn-view:hover { background-color: var(--primary-green); color: white; }

        /* Details Grid Style */
        .details-grid {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            margin-top: 15px;
            align-items: baseline;
        }
        .details-grid strong { color: #555; }

        /* Mobile Responsive Table */
        @media (max-width: 600px) {
            .styled-table thead { display: none; }
            .styled-table, .styled-table tbody, .styled-table tr, .styled-table td { display: block; width: 100%; }
            .styled-table tr { margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; background: white; }
            .styled-table td { text-align: right; padding-left: 50%; position: relative; border-bottom: 1px solid #eee; }
            .styled-table td::before { content: attr(data-label); position: absolute; left: 15px; width: 45%; text-align: left; font-weight: bold; color: var(--primary-green); }
            
            .details-grid { grid-template-columns: 1fr; gap: 5px; }
            .details-grid strong { margin-top: 10px; display: block; }
        }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">My Report Status</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        <a href="UserReportMissingItem.php">Report Missing Item</a>
        <a href="UserMissingItemStatus.php">View Report Status</a>
        <a href="UserEditAccount.php">Edit Account</a>
        <a href="UserContactUs.php">Contact Us</a>
        <a href="Logout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        
        <a href="UserDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="content-container">
            <?php if (isset($is_detail_mode) && $is_detail_mode && $report_details): ?>
                
                <a href="UserMissingItemStatus.php" style="display:inline-block; margin-bottom:15px; color:#666; text-decoration:none;">« Back to List</a>
                
                <div style="background:white; padding:30px; border-radius:8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2 style="color:var(--primary-green); border-bottom: 1px solid #eee; padding-bottom: 15px;">
                        <?php echo htmlspecialchars($report_details['item_name']); ?>
                    </h2>
                    
                    <div class="details-grid">
                        <strong>Report ID:</strong>
                        <span>#<?php echo htmlspecialchars($report_details['report_id']); ?></span>

                        <strong>Status:</strong>
                        <span><span class="status-badge <?php echo $report_details['status']; ?>"><?php echo $report_details['status']; ?></span></span>

                        <strong>Description:</strong>
                        <span><?php echo nl2br(htmlspecialchars($report_details['description'])); ?></span>

                        <strong>Item Level:</strong>
                        <span><?php echo htmlspecialchars($report_details['item_level']); ?></span>

                        <strong>Date Lost:</strong>
                        <span><?php echo htmlspecialchars($report_details['date_lost']); ?></span>

                        <strong>Picture Type:</strong>
                        <span><?php echo htmlspecialchars($report_details['picture_type']); ?></span>

                        <strong>Reported Time:</strong>
                        <span><?php echo htmlspecialchars($report_details['reported_at']); ?></span>
                    </div>

                    <?php if(!empty($report_details['item_picture_path'])): ?>
                        <div style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px;">
                            <strong>Attached Picture:</strong><br>
                            <img src="<?php echo htmlspecialchars($report_details['item_picture_path']); ?>" 
                                 alt="Evidence" 
                                 style="max-width: 100%; max-height: 400px; margin-top: 15px; border-radius: 4px; border: 1px solid #ddd;">
                        </div>
                    <?php endif; ?><?php if ($report_details['status'] === 'Pending'): ?>
    <div style="margin-top: 25px; border-top: 1px solid #eee; padding-top: 20px; text-align: right;">
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this report? This cannot be undone.');">
            <input type="hidden" name="report_id" value="<?php echo $report_details['report_id']; ?>">
            <button type="submit" name="delete_report" class="btn-view" 
                    style="color: #d32f2f; border-color: #d32f2f; background: none; cursor: pointer; padding: 10px 20px;">
                Delete Report
            </button>
        </form>
    </div>
<?php endif; ?>
                </div>

            <?php else: ?>
                <h2 style="margin-bottom: 20px; color: #333;">My Reported Items</h2>
                
                <?php if (empty($all_activity)): ?>
                    <div style="background: white; padding: 30px; border-radius: 8px; text-align: center; color: #666;">
                        <p>You haven't reported any items yet.</p>
                        <br>
                        <a href="UserReportMissingItem.php" class="btn-view" style="background: var(--primary-green); color: white;">Report an Item</a>
                    </div>
                <?php else: ?>
                    <table class="styled-table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Date Reported</th>
                                <th>Status</th>
                                <th style="text-align: center;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_activity as $row): ?>
                            <tr>
                                <td data-label="Item Name"><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                                <td data-label="Date"><?php echo date('Y-m-d', strtotime($row['date'])); ?></td>
                                <td data-label="Status">
                                    <span class="status-badge <?php echo htmlspecialchars($row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Action" style="text-align: center;">
                                    <a href="UserMissingItemStatus.php?view_id=<?php echo $row['id']; ?>" class="btn-view">View Details</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            <?php endif; ?>
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