<?php
session_start();
include "AdminDBConfig.php"; 

if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['type'])) {
    header("Location: AdminHistory.php");
    exit();
}

$res_id = $_GET['id'];
$type = $_GET['type'];
$data = null;

// Updated SQL to fetch item_picture, floor_level, and date_found
if ($type == 'registered') {
    $sql = "SELECT h.*, 
            u.username AS u_name, u.email AS u_email, u.phoneUser AS u_phone, u.id_number AS u_card_id,
            a.AdminName
            FROM founditemreport h
            LEFT JOIN userdata u ON h.user_id = u.id
            LEFT JOIN admindata a ON h.admin_id = a.id
            WHERE h.resolution_id = ?";
} else {
    $sql = "SELECT n.*, 
            n.owner_name AS u_name, n.owner_email AS u_email, n.owner_phone AS u_phone, n.owner_student_staff_id AS u_card_id,
            a.AdminName
            FROM founditemreportfornonuser n
            LEFT JOIN admindata a ON n.admin_id = a.id
            WHERE n.resolution_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $res_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Report not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - History Details</title>
    <style>
        /* --- STYLES PRESERVED EXACTLY --- */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        :root {
            --primary-blue: #1565c0;
            --primary-dark: #0d47a1;
            --sidebar-width: 280px;
            --light-blue-bg: #e3f2fd;
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

        .main-content { margin-top: 60px; padding: 40px 20px; max-width: 1000px; margin-left: auto; margin-right: auto; }
        .back-link { display: inline-block; margin-bottom: 25px; text-decoration: none; color: var(--primary-blue); font-weight: 500; }
        .back-link:hover { text-decoration: underline; }

        .report-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .report-card h1 { color: var(--primary-blue); margin-bottom: 10px; font-size: 2rem; text-align: center; }
        .report-card .subtitle { color: #666; margin-bottom: 40px; text-align: center; font-size: 1.1rem; }

        .section-title { color: var(--primary-dark); font-size: 1.2rem; margin: 30px 0 15px 0; border-bottom: 2px solid var(--light-blue-bg); padding-bottom: 10px; display: block; text-align: left; }

        .info-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px 20px; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; transition: 0.3s; }
        .info-box:hover { border-color: var(--primary-blue); background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .info-label { font-weight: bold; color: #555; font-size: 0.95rem; }
        .info-value { color: var(--primary-blue); font-size: 1.05rem; font-weight: 600; }

        .image-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px; }
        .img-container { text-align: center; background: #fdfdfd; padding: 15px; border: 1px solid #eee; border-radius: 8px; }
        .img-container img { max-width: 100%; border-radius: 4px; margin-top: 10px; border: 1px solid #ddd; }
        .img-label { font-size: 0.9rem; font-weight: bold; color: var(--primary-dark); }

        .badge { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; }
        .badge-reg { background: #e8f5e9; color: #2e7d32; }
        .badge-non { background: #fff3e0; color: #e65100; }

        @media (max-width: 600px) { .image-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">History details</div>
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
        <a href="AdminHistory.php" style="background-color: var(--light-blue-bg); border-left-color: var(--primary-blue); color: var(--primary-blue);">View History</a>
        <a href="AdminContactUS.php">Contact Us</a>
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="AdminHistory.php" class="back-link">← Back to History</a>

        <div class="report-card">
            <h1>Summarized Report</h1>
            <p class="subtitle">Resolution details for ID: #<?php echo htmlspecialchars($res_id); ?></p>

            <h2 class="section-title">👤 Claimant Information</h2>
            <div class="info-box">
                <span class="info-label">Status</span>
                <span class="info-value">
                    <?php if ($type == 'registered'): ?>
                        <span class="badge badge-reg">Registered User</span>
                    <?php else: ?>
                        <span class="badge badge-non">Guest / Non-User</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="info-box">
                <span class="info-label">Full Name</span>
                <span class="info-value"><?php echo htmlspecialchars($data['u_name'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Staff/Student ID</span>
                <span class="info-value"><?php echo htmlspecialchars($data['u_card_id'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Email Address</span>
                <span class="info-value"><?php echo htmlspecialchars($data['u_email'] ?? 'N/A'); ?></span>
            </div>

            <h2 class="section-title">📦 Item Details</h2>
            <div class="info-box">
                <span class="info-label">Item Name</span>
                <span class="info-value"><?php echo htmlspecialchars($data['item_name']); ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Floor Level</span>
                <span class="info-value">Floor <?php echo htmlspecialchars($data['floor_level'] ?? 'N/A'); ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Date Item was Found</span>
                <span class="info-value">
                    <?php echo !empty($data['date_found']) ? date('d M Y', strtotime($data['date_found'])) : 'N/A'; ?>
                </span>
            </div>

            <h2 class="section-title">🖼️ Evidence & Resolution</h2>
            <div class="info-box">
                <span class="info-label">Resolved By</span>
                <span class="info-value"><?php echo htmlspecialchars($data['AdminName']); ?></span>
            </div>
            <div class="info-box">
                <span class="info-label">Date Resolved</span>
                <span class="info-value"><?php echo date('d M Y, h:i A', strtotime($data['resolved_at'])); ?></span>
            </div>

            <div class="image-grid">
                <?php if (!empty($data['item_picture'])): ?>
                    <div class="img-container">
                        <span class="img-label">Found Item Picture</span><br>
                        <img src="<?php echo htmlspecialchars($data['item_picture']); ?>" alt="Found Item">
                    </div>
                <?php endif; ?>

                <?php if (!empty($data['evidence_picture_path'])): ?>
                    <div class="img-container">
                        <span class="img-label">Handover Proof</span><br>
                        <img src="<?php echo htmlspecialchars($data['evidence_picture_path']); ?>" alt="Evidence">
                    </div>
                <?php endif; ?>

                <?php if ($type != 'registered' && !empty($data['owner_id_picture'])): ?>
                    <div class="img-container">
                        <span class="img-label">Owner ID Card</span><br>
                        <img src="<?php echo htmlspecialchars($data['owner_id_picture']); ?>" alt="Owner ID">
                    </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($data['evidence_description'])): ?>
                <h2 class="section-title">📝 Evidence Description</h2>
                <div class="info-box" style="flex-direction: column; align-items: flex-start;">
                    <span class="info-value" style="font-weight: normal; color: #444;">
                        <?php echo htmlspecialchars($data['evidence_description']); ?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if (!empty($data['secret_notes'])): ?>
                <h2 class="section-title">🔒 Internal Admin Notes</h2>
                <div class="info-box" style="background-color: #fff9c4; border-color: #fbc02d;">
                    <span class="info-value" style="color: #5d4037; font-weight: normal; font-style: italic;">
                        <?php echo htmlspecialchars($data['secret_notes']); ?>
                    </span>
                </div>
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

    <?php include '../footer.html'; ?>
</body>
</html>