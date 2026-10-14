<?php
session_start();
include "AdminDBConfig.php"; 

if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

/** * 1. Fetch Registered User History
 * UPDATED: Joined 'found_items' (f) to get floor_level and created_at (found_date)
 */
$sql_user = "SELECT h.resolution_id, h.resolved_at, h.item_name, u.username AS owner_name, a.AdminName AS admin_name,
             f.floor_level, f.created_at AS found_date
             FROM founditemreport h
             LEFT JOIN userdata u ON h.user_id = u.id
             LEFT JOIN admindata a ON h.admin_id = a.id
             LEFT JOIN found_items f ON h.found_item_id = f.id
             ORDER BY h.resolved_at DESC";
$res_user = $conn->query($sql_user);

/** * 2. Fetch Non-User (Guest) History
 * UPDATED: Joined 'found_items' (f) to get floor_level and created_at (found_date)
 */
$sql_guest = "SELECT n.resolution_id, n.resolved_at, n.item_name, n.item_description, a.AdminName AS admin_name,
              f.floor_level, f.created_at AS found_date 
              FROM founditemreportfornonuser n
              LEFT JOIN admindata a ON n.admin_id = a.id
              LEFT JOIN found_items f ON n.found_item_id = f.id
              ORDER BY n.resolved_at DESC";
$res_guest = $conn->query($sql_guest);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Resolution History</title>
   <style>
        /* --- 1. SHARED LAYOUT STYLES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Arial, sans-serif; }
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
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #333; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: var(--light-blue-bg); color: var(--primary-blue); border-left-color: var(--primary-blue); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; }
        .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* Content & Tables */
        .main-content { margin-top: 60px; padding: 30px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        
        .section-box { 
            background: white; 
            padding: 25px; 
            border-radius: 8px; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
            margin: 0 auto 30px auto; /* Centers the box */
            max-width: 1100px;        /* Keeps content focused */
            overflow-x: auto; 
        }
        h1 { color: var(--primary-blue); margin-bottom: 10px; text-align: center; } /* Centered header */
        p.subtitle { text-align: center; margin-bottom: 25px; color: #666; } /* Target for subtitle */
        h2 { font-size: 1.2rem; color: var(--primary-dark); margin-bottom: 15px; border-bottom: 2px solid var(--light-blue-bg); padding-bottom: 8px; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            table-layout: fixed; /* Forces columns to align perfectly */
        }
        th { background-color: var(--primary-blue); color: white; padding: 12px; text-align: left; white-space: nowrap; }
        td { padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle; }
        
        /* Fixed Column Widths for alignment */
        th:nth-child(1), td:nth-child(1) { width: 30%; } /* Item Name */
th:nth-child(2), td:nth-child(2) { width: 20%; } /* Date */
th:nth-child(3), td:nth-child(3) { width: 20%; } /* Admin */
th:nth-child(4), td:nth-child(4) { width: 15%; } /* View Action */
th:nth-child(5), td:nth-child(5) { width: 15%; } /* Delete Action */

.btn-delete {
    background-color: #d32f2f;
    color: white;
    padding: 6px 12px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.9rem;
    display: inline-block;
    text-align: center;
    width: 100%;
    border: none;
    cursor: pointer;
}
.btn-delete:hover { background-color: #b71c1c; }

        .admin-badge { background: var(--light-blue-bg); color: var(--primary-blue); padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; }
        .empty-msg { text-align: center; color: #888; padding: 30px; }
        
        /* Action Button */
        .btn-view {
            background-color: var(--primary-blue); color: white; padding: 6px 12px; 
            text-decoration: none; border-radius: 4px; font-size: 0.9rem; display: inline-block;
            text-align: center; width: 100%; /* Makes button uniform */
        }
        .btn-view:hover { background-color: var(--primary-dark); }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title"><strong>History</strong></div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button style="background:none; border:none; font-size:28px; cursor:pointer;" onclick="toggleMenu()">&times;</button>
        </div>
        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a><a href=" AdminViewAllReports.php"> All lost and found report</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php">View User Accounts</a>
        <a href="AdminHistory.php" style="background-color: var(--light-blue-bg); border-left-color: var(--primary-blue); color: var(--primary-blue);">View History</a>
        <a href="AdminContactUS.php">Contact Us</a>
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>
        <h1>General History</h1>
        

        <div class="section-box">
            <h2>History: Registered Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        
                        <th>Resolved Date</th>
                        
                        <th>Admin</th>
                         <th>View report</th>
                        <th>Delete report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_user && $res_user->num_rows > 0): ?>
                        <?php while($row = $res_user->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                                
                                <td><?php echo date('d M Y', strtotime($row['resolved_at'])); ?></td>
                                
                                <td><span class="admin-badge"><?php echo htmlspecialchars($row['admin_name']); ?></span></td>
                                <td>
                                    <a href="AdminHistoryDetails.php?id=<?php echo $row['resolution_id']; ?>&type=registered" class="btn-view">View</a><td><a href="DeleteHistoryReport.php?id=<?php echo $row['resolution_id']; ?>&type=registered" 
       class="btn-delete" 
       onclick="return confirm('Are you sure you want to delete this history record?')">Delete</a>
</td>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty-msg">No registered user history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-box">
            <h2 style="border-bottom-color: #ffcc80;">History: Non-Users (Guests)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                      
                        <th>Resolved Date</th>
                     
                        <th>Admin</th>
                        <th>View report</th>
                        <th>Delete report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($res_guest && $res_guest->num_rows > 0): ?>
                        <?php while($row = $res_guest->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                                
                                <td><?php echo date('d M Y', strtotime($row['resolved_at'])); ?></td>
                                
                                <td><span class="admin-badge"><?php echo htmlspecialchars($row['admin_name']); ?></span></td>
                                <td>
                                    <a href="AdminHistoryDetails.php?id=<?php echo $row['resolution_id']; ?>&type=guest" class="btn-view">View</a><td>
    <a href="DeleteHistoryReport.php?id=<?php echo $row['resolution_id']; ?>&type=registered" 
       class="btn-delete" 
       onclick="return confirm('Are you sure you want to delete this history record?')">Delete</a>
</td></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="empty-msg">No guest history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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