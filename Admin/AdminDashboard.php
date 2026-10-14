<?php
// Start the session
session_start();

// --- 1. Database Connection Setup ---
// ASSUMPTION: AdminDBConfig.php establishes the connection and sets the $conn variable.
require_once 'AdminDBConfig.php';

if ($conn->connect_error) {
    die("ERROR: Database connection failed: " . $conn->connect_error);
}

// --- 2. Session Management Check ---
if (!isset($_SESSION['AdminID'])) {
    $conn->close();
    header("Location: LoginAdmin.php");
    exit();
}

// --- 3. Fetch Admin Data ---
$admin_db_id = $_SESSION['AdminID'];
$sql = "SELECT AdminName, AdminID FROM admindata WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) { die("Prepare failed: " . htmlspecialchars($conn->error)); }

$stmt->bind_param("i", $admin_db_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_unset();
    session_destroy();
    $conn->close();
    header("Location: LoginAdmin.php?error=user_not_found");
    exit();
}

$admin_data = $result->fetch_assoc();
$stmt->close();
$admin_display_name = htmlspecialchars($admin_data['AdminName'] ?? $admin_data['AdminID']);


// --- 4. Filtering and Sorting Setup ---
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
    // Search item name, description, and category
    $where_clause = "
        WHERE item_name LIKE '$safe_search' 
        OR item_description LIKE '$safe_search'
        OR category LIKE '$safe_search'
    ";
}


// --- 5. Combined Data Fetch using UNION ALL ---
$all_reports = [];
$item_fetch_error = null;

// The core UNION query is wrapped in a subquery to allow filtering/sorting on the aliased columns
$sql_combined = "
    SELECT * FROM (
        -- 1. Found Items
        SELECT 
            id, 
            item_name, 
            floor_level AS item_floor, 
            picture_path, 
            'none' AS item_description,
            'Real' AS picture_type, 
            'Found Item' AS category,
            created_at AS reported_date
        FROM found_items

        UNION ALL

        -- 2. Lost Items (User Submissions)
        SELECT 
            report_id AS id, 
            item_name, 
            item_level AS item_floor,           
            item_picture_path AS picture_path, 
            description AS item_description,
            picture_type, 
            'Lost Item' AS category,
            reported_at AS reported_date        
        FROM missingitems

        UNION ALL

        -- 3. Lost Items (Admin Submissions)
        SELECT 
            report_id AS id, 
            item_name, 
            floor AS item_floor,                
            item_picture_path AS picture_path, 
            description AS item_description,
            picture_type, 
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


// --- 6. Status Message Handling ---
$status_message = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $status_message = '<p style="color: green; font-weight: bold;">✅ Report submitted successfully!</p>';
    } else if ($_GET['status'] == 'error') {
        $status_message = '<p style="color: red; font-weight: bold;">❌ Submission failed. Please try again.</p>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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
            /* DARK BLUE Theme Color */
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
            left: calc(var(--sidebar-width) * -1); /* Hidden */
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
            background-color: #e3f2fd; /* Light Blue */
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

        /* --- 2. DASHBOARD CONTENT STYLES --- */
        
        /* Search Bar */
        .search-controls {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }

        input[type="text"], select {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        button[type="submit"] {
            padding: 8px 16px;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="submit"]:hover { background-color: var(--primary-dark); }

        /* Item Grid */
        .item-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .item-card {
            background: white;
            width: 220px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 5px solid var(--category-color, #999);
        }

        .item-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        .item-card[data-category*="Found"] { --category-color: #4CAF50; /* Green */ }
        .item-card[data-category*="Lost"] { --category-color: #f44336; /* Red */ }

        .item-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
            display: block;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.7);
            overflow: auto;
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
            display: block;
            margin-left: auto; 
            margin-right: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: #000; }

    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Admin Dashboard</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        
        <a href="AdminDashboard.php" style="background-color: #e3f2fd; border-left-color: var(--primary-blue);">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a>
        
       <a href=" AdminViewAllReports.php"> All lost and found report</a>
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
        <h2 style="margin-bottom: 20px; color: var(--primary-blue);">Hello, <?php echo $admin_display_name; ?></h2>
        
        <?php echo $status_message; ?>
        <?php if (isset($item_fetch_error)) echo "<p style='color: red;'>$item_fetch_error</p>"; ?>

        <div class="search-controls">
            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: flex; gap: 5px;">
                <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search_term); ?>">
                <button type="submit">Search</button>
                <?php if (!empty($search_term)): ?>
                    <a href="AdminDashboard.php?sort=<?php echo htmlspecialchars($sort_by); ?>" style="text-decoration: none; padding: 8px 12px; border: 1px solid #ccc; color: #333; background-color: #eee; border-radius: 4px;">Clear</a>
                <?php endif; ?>
            </form>

            <form method="get" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" style="display: flex; gap: 5px; align-items: center;">
                <?php if (!empty($search_term)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <?php endif; ?>
                
                <label for="sort" style="font-weight: 500;">Sort By:</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <option value="reported_date_desc" <?php if ($sort_by == 'reported_date_desc') echo 'selected'; ?>>Newest First</option>
                    <option value="reported_date_asc" <?php if ($sort_by == 'reported_date_asc') echo 'selected'; ?>>Oldest First</option>
                    <option value="name_asc" <?php if ($sort_by == 'name_asc') echo 'selected'; ?>>Name (A-Z)</option>
                    <option value="floor_asc" <?php if ($sort_by == 'floor_asc') echo 'selected'; ?>>Floor (Low to High)</option>
                    <option value="category_asc" <?php if ($sort_by == 'category_asc') echo 'selected'; ?>>Category</option>
                </select>
            </form><div style="margin: 10px 0;">
        <form action="AdminNotification.php" method="POST">
            <button type="submit" name="notify" 
                style="background-color: #1565c0; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                🔔 
            </button></div><p> 
        </form><p style="margin-top: 10px;">
        <span style="color: green; font-weight: bold;">● Found Item</span> . 
        <span style="color: red; font-weight: bold;">● Lost Item</span>
        </form><p>
    
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
                        <span style="font-size: 0.9em; color: #555;">
                            Floor: <?php echo htmlspecialchars($item['item_floor']); ?><br>
                            Type: <?php echo htmlspecialchars($item['category']); ?><br>
                            <?php echo $display_date; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="padding: 10px; color: #666;">No items found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </main>

    <div id="itemModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3 id="modalItemName" style="color: var(--primary-blue); margin-bottom: 15px;"></h3>
            
            <img id="modalItemPicture" src="" alt="Item Picture">
            
            <div style="background: #fafafa; padding: 15px; border-radius: 4px; border: 1px solid #eee;">
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
        // Sidebar Toggle
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Modal Logic
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

<?php
// Close the database connection at the very end of the script
$conn->close();
?>
<?php include '../footer.html'; ?>