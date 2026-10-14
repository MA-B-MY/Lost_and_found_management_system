<?php
session_start();
include "UserDBConfig.php";

if (!isset($_SESSION['username'])) { 
    header("Location: LoginUser.php"); 
    exit(); 
}

$username = $_SESSION['username'];
$search = $_GET['search'] ?? '';

// 1. Handle Notification Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['selected_item'])) {
    $item_name = $_POST['selected_item'];
    $message = "User '$username' will come to claim '$item_name' within 24 hours.";

    $stmt = $conn->prepare("INSERT INTO admin_notifications (username, item_name, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $item_name, $message);
    $stmt->execute();
    header("Location: UserDashboard.php?status=success");
    exit();
}

// 2. Fetch Items from 'found_items'
$sql = "SELECT item_name, picture_path FROM found_items";
if (!empty($search)) {
    $safe_search = "%" . $conn->real_escape_string($search) . "%";
    $sql .= " WHERE item_name LIKE '$safe_search'";
}
$items = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Item - Item Tracker</title>
    <style>
        /* --- SHARED LAYOUT STYLES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        
        :root {
            --primary-green: #004d40; 
            --light-green-bg: #e0f2f1;
            --sidebar-width: 280px;
        }

        body { background-color: #f4f6f8; color: #333; }

        /* Header */
        .header { background-color: var(--primary-green); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; }
        .header-title { font-size: 1.2rem; font-weight: 500; }

        /* Sidebar */
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px 20px; font-size: 1.5rem; color: var(--primary-green); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: var(--light-green-bg); color: var(--primary-green); border-left: 5px solid var(--primary-green); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; margin-bottom: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* Main Content */
        .main-content { margin-top: 60px; padding: 40px 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        
        /* Search Bar */
        .search-box { display: flex; gap: 15px; margin-bottom: 40px; }
        .search-box input { flex-grow: 1; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 18px; }
        .search-box button { width: 150px; background: var(--primary-green); color: white; border: none; border-radius: 8px; font-size: 18px; cursor: pointer; font-weight: bold; }

        /* Item Rows */
        .item-list { display: grid; grid-template-columns: 1fr; gap: 20px; }
        .item-row { display: flex; align-items: center; padding: 25px; border: 2px solid #eee; border-radius: 12px; cursor: pointer; transition: 0.3s; }
        .item-row:hover { border-color: var(--primary-green); background: #f9fffb; }
        .item-row input { transform: scale(2); margin-right: 35px; }
        .item-row img { width: 180px; height: 180px; object-fit: cover; border-radius: 10px; margin-right: 35px; border: 1px solid #ccc; }
        .item-info { font-size: 24px; font-weight: bold; color: #222; }
        
        .submit-btn { width: 100%; padding: 20px; background: #f39c12; color: white; border: none; border-radius: 10px; font-size: 22px; font-weight: bold; cursor: pointer; margin-top: 30px; }
        .submit-btn:hover { background: #e67e22; }
        .back-link { display: block; text-align: center; margin-top: 20px; text-decoration: none; color: var(--primary-green); font-weight: bold; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Item Tracker</div>
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

    <main class="main-content"><div style="max-width: 1000px; margin: 0 auto 15px auto;">
        <a href="UserDashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
        <div class="container">
            <h1 style="color: var(--primary-green); margin-bottom: 10px;">Claim Found Item</h1>
            <p style="font-size: 18px; color: #555; margin-bottom: 30px;">Select the item you lost to notify the admin of your 24h arrival.</p>

            <form method="GET" class="search-box">
                <input type="text" name="search" placeholder="Enter item name..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>

            <form method="POST">
                <div class="item-list">
                    <?php if ($items && $items->num_rows > 0): ?>
                        <?php while($row = $items->fetch_assoc()): 
                            $img = !empty($row['picture_path']) ? htmlspecialchars($row['picture_path']) : 'images/default.png';
                        ?>
                            <label class="item-row">
                                <input type="radio" name="selected_item" value="<?php echo htmlspecialchars($row['item_name']); ?>" required>
                                <img src="<?php echo $img; ?>" alt="Item Image">
                                <div class="item-info"><?php echo htmlspecialchars($row['item_name']); ?></div>
                            </label>
                        <?php endwhile; ?>
                        <button type="submit" class="submit-btn" onclick="return confirm('Are you sure you want to notify the admin? This confirms you will arrive within 24 hours.')">
    📢 Notify Admin of My Arrival
</button>
                    <?php else: ?>
                        <p style="text-align:center; font-size: 20px; padding: 40px; color: #888;">No found items match your search.</p>
                    <?php endif; ?>
                </div>
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