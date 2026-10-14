<!DOCTYPE html> 
<html>
<head>
    <title>Report Missing Item</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- 1. SHARED LAYOUT STYLES (Header, Sidebar, Reset) --- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            --primary-green: #004d40; /* Dark Green */
            --sidebar-width: 280px;
        }

        body {
            background-color: #f4f6f8;
            color: #333;
        }

        /* Fixed Header */
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

        /* Overlay */
        .overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }
        .overlay.active { display: block; }

        /* Main Content Wrapper */
        .main-content {
            margin-top: 60px;
            padding: 20px;
        }

        /* --- 2. FORM SPECIFIC STYLES --- */
        
        /* Back Link Styling */
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: var(--primary-green);
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }

        /* Form Card */
        .form-card {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            /* Centering the form for better UX, though page is full width */
            max-width: 600px; 
            margin: 0 auto; 
        }

        .form-card h1 {
            text-align: center;
            margin-bottom: 25px;
            color: var(--primary-green);
            font-size: 1.8rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        /* Inputs */
        input[type="text"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-green);
            box-shadow: 0 0 3px rgba(0, 77, 64, 0.2);
        }

        /* Radio Buttons Group */
        .radio-group {
            margin-bottom: 20px;
            background: #fafafa;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        
        .radio-option {
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .radio-option input { margin: 0; cursor: pointer; }
        .radio-option label { margin: 0; font-weight: normal; cursor: pointer; }

        /* File Upload */
        input[type="file"] {
            margin-bottom: 20px;
            padding: 10px 0;
        }

        /* Submit Button */
        input[type="submit"] {
            width: 100%;
            background-color: var(--primary-green);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #00382e;
        }

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
        
        <a href="UserReportMissingItem.php" style="background-color: #e0f2f1; border-left-color: var(--primary-green);">Report Missing Item</a>
        <a href="UserMissingItemStatus.php">View Report Status</a>
        <a href="UserEditAccount.php">Edit Account</a>
        <a href="UserContactUs.php">Contact Us</a>
        
        
        <a href="Logout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        
        <a href="UserDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="form-card">
            <h1>Report Missing Item</h1>
            
            <form action="UserReportProcess.php" method="POST" enctype="multipart/form-data">
                
                <label for="item_name">Item Name:</label>
                <input type="text" id="item_name" name="item_name" placeholder="E.g., Blue Water Bottle" required>

                <label for="level">Level Lost:</label>
                <select id="level" name="level" required>
                    <option value="">Select Level</option>
                    <?php
                    for ($i = 1; $i <= 25; $i++) {
                        echo "<option value=\"$i\">Level $i</option>";
                    }
                    ?>
                </select>

                <label for="date_lost">Date Lost:</label>
                <input type="date" id="date_lost" name="date_lost" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="5" placeholder="Describe colour, brand, specific markings, or last known location..." required></textarea>

                <label>Picture Type:</label>
                <div class="radio-group">
                    <p style="font-size: 0.9em; color: #666; margin-bottom: 10px;">Is this the real picture or a placeholder?</p>
                    
                    <div class="radio-option">
                        <input type="radio" id="pic_real" name="picture_type" value="Real" required>
                        <label for="pic_real">Real Picture (Photo of the actual item)</label>
                    </div>
                    
                    <div class="radio-option">
                        <input type="radio" id="pic_placeholder" name="picture_type" value="Placeholder" required>
                        <label for="pic_placeholder">Placeholder Picture (Looks similar to item)</label>
                    </div>
                </div>

                <label for="item_picture">Upload Picture:</label>
                <input type="file" id="item_picture" name="item_picture" accept="image/*">

                <input type="submit" value="Report Missing Item">

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
<?php include '../footer.html'; ?>