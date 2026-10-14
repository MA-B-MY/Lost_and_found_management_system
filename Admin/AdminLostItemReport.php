<?php
session_start(); // START SESSION
require_once 'AdminDBConfig.php';

// Retrieve Admin details from Session (Fixing hardcoded values)
$admin_name = $_SESSION['admin_name'] ?? 'Admin'; // Default if not set
$admin_id = $_SESSION['admin_id'] ?? 'A0000';    // Default if not set

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Logic moved to AdminLostItemReportProcess.php
    // This block is technically redundant if you are submitting to Process.php
    // but kept if you want self-processing.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Lost Item Report</title>
    <style>
        /* ... (Your existing styles remain unchanged) ... */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root { --primary-blue: #1565c0; --primary-dark: #0d47a1; --sidebar-width: 280px; }
        body { background-color: #f4f6f8; color: #333; }
        .header { background-color: var(--primary-blue); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; padding: 5px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .menu-btn:hover { background-color: rgba(255,255,255,0.2); }
        .header-title { font-size: 1.2rem; font-weight: 500; }
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: #e3f2fd; color: var(--primary-blue); border-left: 5px solid var(--primary-blue); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; margin-bottom: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }
        .main-content { margin-top: 60px; padding: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .form-card h1 { color: var(--primary-blue); font-size: 1.8rem; margin-bottom: 25px; text-align: center; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        input[type="text"], select, textarea { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        input[type="text"]:focus, select:focus, textarea:focus { outline: none; border-color: var(--primary-blue); box-shadow: 0 0 3px rgba(21, 101, 192, 0.2); }
        fieldset { border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; margin-bottom: 20px; background-color: #fafafa; }
        legend { font-weight: bold; color: var(--primary-blue); padding: 0 5px; }
        input[type="radio"], input[type="checkbox"] { margin-right: 8px; transform: scale(1.1); cursor: pointer; }
        .radio-label { font-weight: normal; display: inline-block; margin-right: 15px; cursor: pointer; }
        input[type="submit"] { width: 100%; background-color: var(--primary-blue); color: white; padding: 14px; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        input[type="submit"]:hover { background-color: var(--primary-dark); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid transparent; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border-color: #c8e6c9; }
        .alert-error { background-color: #ffebee; color: #c62828; border-color: #ffcdd2; }
        .alert-info { background-color: #e3f2fd; color: #1565c0; border-color: #bbdefb; }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Create Lost Item Report</div>
    </header>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span>Admin Menu</span>
            <button class="close-btn" onclick="toggleMenu()">&times;</button>
        </div>
        
        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a><a href=" AdminViewAllReports.php"> All lost and found report</a>
        <a href="AdminLostItemReport.php" style="background-color: #e3f2fd; border-left-color: var(--primary-blue);">Create Lost Report</a>
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

        <div class="form-card">
            <h1>Create Lost Item Report</h1>

            <?php 
            if (isset($_GET['msg'])) {
                $status = $_GET['status'] ?? 'info';
                $alertClass = ($status == 'success') ? 'alert-success' : 'alert-error';
                echo "<div class='alert $alertClass'><strong>" . htmlspecialchars(urldecode($_GET['msg'])) . "</strong></div>";
            }
            ?>

            <form action="AdminLostItemReportProcess.php" method="post" enctype="multipart/form-data">

                <label for="item_name">Item Name:</label>
                <input type="text" id="item_name" name="item_name" required>

                <label for="floor">Floor:</label>
                <select id="floor" name="floor" required>
                    <?php for ($i = 1; $i <= 25; $i++): ?>
                        <option value="<?php echo $i; ?>">Level <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="4" required></textarea>

                <label for="item_picture">Upload Picture:</label>
                <input type="file" id="item_picture" name="item_picture" accept="image/*" style="margin-bottom: 20px;">

                <fieldset>
                    <legend>Image Type</legend>
                    <div style="margin-top: 10px;">
                        <input type="radio" id="type_photo" name="picture_type" value="Photo" required>
                        <label for="type_photo" class="radio-label">Real Item Picture</label>
                        <br>
                        <input type="radio" id="type_sketch" name="picture_type" value="Sketch">
                        <label for="type_sketch" class="radio-label">Similar Picture (Placeholder)</label>
                    </div>
                </fieldset>

                <div style="margin-bottom: 20px; padding: 10px; background-color: #f0f4c3; border-radius: 4px;">
                    <input type="checkbox" id="report_by_admin" name="report_by_admin" onchange="toggleReportedByFields()">
                    <label for="report_by_admin" style="display: inline; cursor: pointer;"><strong>Reported by Admin (Me)</strong></label>
                </div>

                <fieldset id="reported_by_fields">
                    <legend>Reported By Details (If Not Admin)</legend>
                    
                    <label for="reported_by_name">Reported By Name:</label>
                    <input type="text" id="reported_by_name" name="reported_by_name" required>

                    <label for="reported_by_id">ID:</label>
                    <input type="text" id="reported_by_id" name="reported_by_id" required>
                </fieldset>

                <input type="submit" value="Submit Report">
                
            </form>
        </div>

    </main>

    <script>
        // Sidebar Toggle
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // Form Logic
        function toggleReportedByFields() {
            const checkbox = document.getElementById('report_by_admin');
            const fields = document.getElementById('reported_by_fields');
            const nameInput = document.getElementById('reported_by_name');
            const idInput = document.getElementById('reported_by_id');

            if (checkbox.checked) {
                // Disable and clear fields when checked
                fields.style.display = 'none';
                nameInput.removeAttribute('required');
                idInput.removeAttribute('required');
            } else {
                // Enable fields when unchecked
                fields.style.display = 'block';
                nameInput.setAttribute('required', 'required');
                idInput.setAttribute('required', 'required');
            }
        }
        
        // Run on page load to set initial state
        toggleReportedByFields();
    </script>

</body>
</html>
<?php include '../footer.html'; ?>