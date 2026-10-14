<?php
session_start();
// ASSUMPTION: UserDBConfig.php establishes the connection and sets the $conn variable.
include "UserDBConfig.php";

// --- 0. Initial Setup & Connection Check ---
if (!$conn) {
    header("Location: LoginUser.php?error=db_fail");
    exit();
}

if (!isset($_SESSION['username'])) {
    $conn->close();
    header("Location: LoginUser.php");
    exit();
}

$username = $_SESSION['username'];

// 1. Fetch User Data (For welcome message and USER ID)
$sql = "SELECT id FROM UserData WHERE username = ?";
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
$user_id = $user_data['id'];


// --- 2. Filtering and Sorting Setup ---
$sort_by = $_GET['sort'] ?? 'reported_date_desc'; // Default sort
$search_term = $_GET['search'] ?? '';

$order_clause = "ORDER BY reported_date DESC";
$where_clause = "";

// Handle Sorting
switch ($sort_by) {
    case 'name_asc':
        $order_clause = "ORDER BY item_name ASC";
        break;
    case 'floor_asc':
        $order_clause = "ORDER BY item_floor ASC";
        break;
    case 'category_asc':
        $order_clause = "ORDER BY category ASC";
        break;
    case 'reported_date_asc':
        $order_clause = "ORDER BY reported_date ASC";
        break;
    case 'reported_date_desc':
    default:
        $order_clause = "ORDER BY reported_date DESC";
        break;
}

// Handle Searching
if (!empty($search_term)) {
    $safe_search = "%" . $conn->real_escape_string($search_term) . "%";
    $where_clause = "
        WHERE item_name LIKE '$safe_search' 
        OR item_description LIKE '$safe_search'
        OR category LIKE '$safe_search'
    ";
}


// --- 3. Combined Data Fetch (SYNCED WITH ADMIN DATA) ---
$all_reports = [];
$item_fetch_error = null;

$sql_combined = "
    SELECT * FROM (
        SELECT 
            report_id AS id, 
            item_name, 
            item_level AS item_floor,            
            item_picture_path AS picture_path, 
            description AS item_description,
            picture_type, -- Added this line
            'Lost Item' AS category,
            reported_at AS reported_date        
        FROM missingitems
        
        UNION ALL

        SELECT 
            id, 
            item_name, 
            floor_level AS item_floor, 
            picture_path, 
            'none' AS item_description, 
            'Real' AS picture_type, -- Placeholder for found items
            'Found Item' AS category,
            created_at AS reported_date
        FROM found_items
        
UNION ALL

        SELECT 
            report_id AS id, 
            item_name, 
            floor AS item_floor,                
            item_picture_path AS picture_path, 
            description AS item_description,
            picture_type, -- Add this
            'Lost Item' AS category,
            report_date AS reported_date
        FROM missingitemadminside
   
    ) AS combined_reports
    $where_clause
    $order_clause
";

$result_combined = $conn->query($sql_combined);

if ($result_combined) {
    while ($row = $result_combined->fetch_assoc()) {
        $all_reports[] = $row;
    }
} else {
    $item_fetch_error = "Error fetching reports: " . $conn->error;
}


// --- 4. Status Message Handling ---
$status_message = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $status_message = '<p style="color: green; font-weight: bold;">✅ Report submitted successfully!</p>';
    } else if ($_GET['status'] == 'error') {
        $status_message = '<p style="color: red; font-weight: bold;">❌ Submission failed. Please try again.</p>';
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            --primary-green: #004d40; 
            --sidebar-width: 280px;
        }

        body {
            background-color: #f4f6f8;
        }

        .header {
            background-color: var(--primary-green);
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
            color: var(--primary-green);
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
            background-color: #e0f2f1;
            color: var(--primary-green);
            border-left: 5px solid var(--primary-green);
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

        .main-content {
            margin-top: 60px;
            padding: 20px;
        }

        .search-controls {
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .search-controls input[type="text"], 
        .search-controls select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .search-controls button {
            padding: 8px 15px;
            background-color: var(--primary-green);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .item-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        .item-card {
            background: white;
            border: 1px solid #eee;
            padding: 10px;
            width: 220px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            border-radius: 8px;
            transition: transform 0.2s;
            border-left: 5px solid var(--category-color, #999); 
        }

        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .item-card[data-category*="Found"] { --category-color: #4CAF50; }
        .item-card[data-category*="Lost"] { --category-color: #f44336; }

        .item-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        .modal {
            display: none; 
            position: fixed; 
            z-index: 2000;
            left: 0; top: 0; 
            width: 100%; height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }

        .modal-content img {
            max-width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: black; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Welcome, <?php echo htmlspecialchars($username); ?></div>
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
        <h2>All Lost and Found Items</h2>
        <div style="margin: 10px 0;">
        <form action="NotifyAdmin.php" method="POST">
            <button type="submit" name="notify_claim" 
                style="background-color: #004d40; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                📢 Notify Admin: Coming to claim found item within 24h
            </button>
        </form>
    </div>
        <?php echo $status_message; ?>
        <?php if (isset($item_fetch_error)) echo "<p style='color: red;'>$item_fetch_error</p>"; ?>

        <div class="search-controls">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: flex; gap: 5px;">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
                <?php if (!empty($search_term)): ?>
                    <a href="UserDashboard.php?sort=<?php echo htmlspecialchars($sort_by); ?>" style="text-decoration: none; padding: 8px 12px; border: 1px solid #ccc; color: #333; background-color: #eee; border-radius: 4px;">Clear</a>
                <?php endif; ?>
            </form>

            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: flex; gap: 5px; align-items: center;">
                <?php if (!empty($search_term)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <?php endif; ?>
                
                <label for="sort">Sort By:</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="reported_date_desc" <?php if ($sort_by == 'reported_date_desc') echo 'selected'; ?>>Newest First</option>
                    <option value="reported_date_asc" <?php if ($sort_by == 'reported_date_asc') echo 'selected'; ?>>Oldest First</option>
                    <option value="name_asc" <?php if ($sort_by == 'name_asc') echo 'selected'; ?>>Name (A-Z)</option>
                    <option value="floor_asc" <?php if ($sort_by == 'floor_asc') echo 'selected'; ?>>Floor (Low to High)</option>
                    <option value="category_asc" <?php if ($sort_by == 'category_asc') echo 'selected'; ?>>Category</option>
                </select>
            </form><p style="margin-top: 10px;">
        <span style="color: green; font-weight: bold;">● Found Item</span> . 
        <span style="color: red; font-weight: bold;">● Lost Item</span>
    </p>
        </div>

        <div class="item-container">
            <?php if (!empty($all_reports)): ?>
                  <?php foreach ($all_reports as $item): 
                    $image_path = !empty($item['picture_path']) ? htmlspecialchars($item['picture_path']) : 'images/default.png';
                    $display_date = date('Y-m-d | H:i', strtotime($item['reported_date']));
                ?>
                    <div class="item-card" 
                         data-category="<?php echo htmlspecialchars($item['category']); ?>"
                         onclick="openModal(
                            '<?php echo htmlspecialchars($item['item_name']); ?>', 
                            '<?php echo htmlspecialchars($item['item_floor']); ?>', 
                            '<?php echo $image_path; ?>', 
                            '<?php echo htmlspecialchars($item['item_description']); ?>',
                            '<?php echo htmlspecialchars($item['category']); ?>',
                            '<?php echo $display_date; ?>',
                            '<?php echo htmlspecialchars($item['picture_type']); ?>'
                         )">
                        
                        <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                        <strong><?php echo htmlspecialchars($item['item_name']); ?></strong><br>
                        <span style="color: #666; font-size: 0.9em;">
                            Floor: <?php echo htmlspecialchars($item['item_floor']); ?><br>
                            Type: <?php echo htmlspecialchars($item['category']); ?><br>
                            <?php echo $display_date; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="padding: 20px;">No items found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalItemName" style="margin-bottom: 15px; color: var(--primary-green);"></h3>
            
            <div style="text-align: center;">
                <img id="modalItemPicture" src="" alt="Item Picture">
            </div>
            
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px;">
                <p><strong>Category:</strong> <span id="modalItemCategory"></span></p>
                <p><strong>Floor:</strong> <span id="modalItemFloor"></span></p>
                <p><strong>Picture Type:</strong> <span id="modalPictureType"></span></p>
                <p><strong>Date Reported:</strong> <span id="modalReportDate"></span></p>
                <hr style="margin: 10px 0; border: 0; border-top: 1px solid #ddd;">
                <p><strong>Description:</strong><br><span id="modalItemDescription"></span></p>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        var modal = document.getElementById("itemModal");
        
        function openModal(name, floor, picture, notes, category, reportDate, picType) { 
            document.getElementById('modalItemName').textContent = name;
            document.getElementById('modalItemPicture').src = picture;
            document.getElementById('modalItemFloor').textContent = floor;
            document.getElementById('modalItemDescription').textContent = notes;
            document.getElementById('modalItemCategory').textContent = category;
            document.getElementById('modalReportDate').textContent = reportDate;
document.getElementById('modalPictureType').textContent = picType;
    
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>

</body>
</html>
<?php $conn->close(); ?>
<?php include '../footer.html'; ?>