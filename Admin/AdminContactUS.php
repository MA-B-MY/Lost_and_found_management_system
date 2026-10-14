<?php
session_start();
// include "AdminDBConfig.php"; 

// 1. Session Security Check for Admin
if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Contact Us Info</title>
    <style>
        /* --- 1. SHARED LAYOUT STYLES --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root {
            --primary-blue: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-width: 280px;
            --light-blue-bg: #e3f2fd;
        }
        body { background-color: #f4f6f8; color: #333; }

        /* Header */
        .header { background-color: var(--primary-blue); color: white; height: 60px; display: flex; align-items: center; padding: 0 20px; position: fixed; top: 0; width: 100%; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .menu-btn { background: none; border: none; color: white; font-size: 24px; cursor: pointer; margin-right: 20px; padding: 5px; }
        .header-title { font-size: 1.2rem; font-weight: 500; }

        /* Sidebar */
        .sidebar { height: 100vh; width: var(--sidebar-width); position: fixed; top: 0; left: calc(var(--sidebar-width) * -1); background-color: #ffffff; transition: left 0.3s ease-in-out; z-index: 1001; box-shadow: 2px 0 10px rgba(0,0,0,0.1); display: flex; flex-direction: column; padding-top: 10px; }
        .sidebar.active { left: 0; }
        .sidebar-header { padding: 10px 20px 20px 20px; font-size: 1.5rem; color: var(--primary-blue); font-weight: bold; display: flex; justify-content: space-between; align-items: center; }
        .close-btn { background: none; border: none; font-size: 28px; cursor: pointer; color: #555; }
        .sidebar a { padding: 15px 25px; text-decoration: none; font-size: 16px; color: #333; display: block; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover { background-color: var(--light-blue-bg); color: var(--primary-blue); border-left: 5px solid var(--primary-blue); }
        .logout-link { margin-top: auto; border-top: 1px solid #eee; color: #d32f2f !important; margin-bottom: 20px; }
        .overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; z-index: 999; }
        .overlay.active { display: block; }

        /* Main Content */
        .main-content { margin-top: 60px; padding: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        .back-link:hover { text-decoration: underline; }

        /* Contact Card Styling */
        .contact-card {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }

        .contact-card h1 { color: var(--primary-blue); margin-bottom: 10px; font-size: 2rem; }
        .contact-card p { color: #666; margin-bottom: 30px; font-size: 1.1rem; }

        .section-title {
            color: var(--primary-dark);
            font-size: 1.2rem;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--light-blue-bg);
            padding-bottom: 10px;
            display: inline-block;
            width: 100%;
            text-align: left;
        }

        /* Info Rows */
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: 0.3s;
        }
        
        .info-box:hover {
            border-color: var(--primary-blue);
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .info-label { font-weight: bold; color: #555; font-size: 1rem; }
        .info-value { color: var(--primary-blue); font-size: 1.1rem; font-weight: 600; }

        /* Address Box */
        .address-box {
            background-color: var(--light-blue-bg);
            color: var(--primary-dark);
            padding: 20px;
            border-radius: 8px;
            line-height: 1.6;
            font-size: 1rem;
            border: 1px solid #bbdefb;
            text-align: left;
        }

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
        
        <a href="AdminDashboard.php">Dashboard</a>
        <a href="AdminEditAccount.php">Edit Admin Account</a>
        <a href="AdminLostItemReport.php">Create Lost Report</a>
        <a href="AdminFoundItemReport.php">Create Found Report</a>
        <a href="AdminFoundOwnerItem.php">Found Owner Report</a>
        <a href="ViewUserAccount.php">View User Accounts</a>
        <a href=" AdminHistory.php"> View History</a>
        <a href="AdminContactUS.php" style="background-color: var(--light-blue-bg); border-left-color: var(--primary-blue); color: var(--primary-blue);">Contact Us</a>
        
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="contact-card">
            
            <h1>Contact Us Information</h1>
            <p>This is the contact information currently displayed to users.</p>

            <h2 class="section-title">Contact Details</h2>

            <div class="info-box">
                <span class="info-label">📧 Official Email</span>
                <span class="info-value">HelpDeskCompanyA@gmail.com</span>
            </div>

            <div class="info-box">
                <span class="info-label">📞 Helpdesk Line</span>
                <span class="info-value">+60 1234 5799</span>
            </div>

            <br>

            <h2 class="section-title">Center Location</h2>

            <div class="address-box">
            <strong> Person In charge : </strong><br>
                Kael Hadief Bin Azlan <br>
                [System Administrator]
                <br>
Monday - Friday
<br>
9:00 - 16:00
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
</html><?php include '../footer.html'; ?>