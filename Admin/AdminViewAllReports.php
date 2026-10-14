<?php
session_start();
require_once 'AdminDBConfig.php';

// --- 1. Session Check ---
if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

// --- 2. Deletion Logic ---
if (isset($_POST['delete_report'])) {
    $id = $_POST['target_id'];
    $category = $_POST['target_category'];

    if ($category === 'Found Item') {
        $stmt = $conn->prepare("DELETE FROM found_items WHERE id = ?");
    } elseif ($category === 'Lost Item (User)') {
        $stmt = $conn->prepare("DELETE FROM missingitems WHERE report_id = ?");
    } else {
        $stmt = $conn->prepare("DELETE FROM missingitemadminside WHERE report_id = ?");
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: AdminViewAllReports.php?msg=deleted");
    exit();
}

// --- 3. Combined Data Fetch ---
$sql_combined = "
    SELECT * FROM (
        SELECT id, item_name, floor_level AS floor, created_at AS date, 'Found Item' AS category FROM found_items
        UNION ALL
        SELECT report_id AS id, item_name, item_level AS floor, reported_at AS date, 'Lost Item (User)' AS category FROM missingitems
        UNION ALL
        SELECT report_id AS id, item_name, floor AS floor, report_date AS date, 'Lost Item (Admin)' AS category FROM missingitemadminside
    ) AS all_posts ORDER BY date DESC";

$result = $conn->query($sql_combined);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage All Reports</title>
    <style>
        /* --- Styles from Contact Us (Sidebar/Header) --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root {
            --primary-blue: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-width: 280px;
            --light-blue-bg: #e3f2fd;
            --danger-red: #d32f2f;
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

        .main-content { margin-top: 80px; padding: 20px; }

        /* --- Original Table Styles --- */
        .styled-table {
            width: 100%; max-width: 1200px; margin: 0 auto;
            border-collapse: collapse; background: white;
            border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .styled-table thead tr { background: var(--primary-blue); color: white; text-align: left; }
        .styled-table th, .styled-table td { padding: 15px 20px; border-bottom: 1px solid #eee; }
        
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .found { background: #e8f5e9; color: #2e7d32; }
        .lost { background: #ffebee; color: #c62828; }

        .btn-delete {
            background: none; border: 1px solid var(--danger-red);
            color: var(--danger-red); padding: 6px 12px;
            border-radius: 4px; cursor: pointer; transition: 0.3s;
        }
        .btn-delete:hover { background: var(--danger-red); color: white; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Delete Active Lost and Found Items </div>
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
        <a href="AdminHistory.php">View History</a>
        <a href="AdminContactUS.php">Contact Us</a>
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content"><a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Item Name</th>
                    <th>Floor</th>
                    <th>Category</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date('Y-m-d', strtotime($row['date'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($row['item_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['floor']); ?></td>
                    <td>
                        <span class="badge <?php echo (strpos($row['category'], 'Found') !== false) ? 'found' : 'lost'; ?>">
                            <?php echo $row['category']; ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Permanently delete this post?');">
                            <input type="hidden" name="target_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="target_category" value="<?php echo $row['category']; ?>">
                            <button type="submit" name="delete_report" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
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