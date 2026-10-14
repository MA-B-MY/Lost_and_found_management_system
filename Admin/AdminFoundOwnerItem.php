<?php
session_start();
include "AdminDBConfig.php";

// 1. Session Security Check
if (!isset($_SESSION['AdminID'])) {
    header("Location: LoginAdmin.php");
    exit();
}

// 2. Fetch Active Found Items for the Modal
// Included floor_level and created_at (to be used as date_found)
$found_items_query = "SELECT id, item_name, floor_level, secret_notes, picture_path, created_at FROM found_items ORDER BY created_at DESC";
$found_items_result = $conn->query($found_items_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Found Owner Report</title>
    <style>
        /* --- 1. SHARED LAYOUT STYLES (From Reference) --- */
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

        /* Form Card */
        .form-card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .form-card h1 { color: var(--primary-blue); font-size: 1.8rem; margin-bottom: 25px; text-align: center; }
        
        /* Inputs & Labels */
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; }
        .form-section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .form-section h3 { color: #555; font-size: 1.1rem; margin-bottom: 15px; border-left: 4px solid var(--primary-blue); padding-left: 10px; }

        input[type="text"], input[type="email"], select, textarea { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; }
        input[type="text"]:focus, textarea:focus { outline: none; border-color: var(--primary-blue); box-shadow: 0 0 3px rgba(21, 101, 192, 0.2); }
        input[type="file"] { margin-bottom: 15px; width: 100%; }
        
        /* Buttons */
        .btn-primary { width: 100%; background-color: var(--primary-blue); color: white; padding: 14px; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        .btn-primary:hover { background-color: var(--primary-dark); }
        
        .btn-secondary { background: white; border: 2px solid var(--primary-blue); color: var(--primary-blue); padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600; transition: 0.2s; }
        .btn-secondary:hover { background: var(--light-blue-bg); }

        .btn-small-action { font-size: 0.9rem; padding: 5px 10px; cursor: pointer; background: #eee; border: 1px solid #ccc; border-radius: 4px; }

        /* --- 2. PAGE SPECIFIC STYLES --- */
        .hidden { display: none; }

        /* Selected Item Preview Box */
        .selected-item-preview { display: flex; align-items: flex-start; gap: 20px; background: var(--light-blue-bg); padding: 15px; border-radius: 6px; border: 1px solid #bbdefb; margin-top: 15px; }
        .selected-item-preview img { width: 100px; height: 100px; object-fit: cover; background: white; border-radius: 4px; border: 1px solid #ddd; }
        .secret-note-box { background: #fff3cd; padding: 8px; border: 1px solid #ffeeba; border-radius: 4px; margin-top: 5px; font-size: 0.9rem; color: #856404; }

        /* Search Results */
        .search-container { display: flex; gap: 10px; margin-bottom: 10px; }
        .user-result-item { padding: 10px; border: 1px solid #eee; border-radius: 4px; margin-top: 5px; cursor: pointer; transition: 0.2s; }
        .user-result-item:hover { background-color: #f5f5f5; border-color: #ccc; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); }
        .modal-content { background-color: white; margin: 5% auto; padding: 25px; border-radius: 8px; width: 90%; max-width: 800px; max-height: 80vh; overflow-y: auto; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .modal-header h2 { color: var(--primary-blue); font-size: 1.5rem; }
        
        .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
        .picker-card { border: 1px solid #ddd; border-radius: 6px; padding: 10px; text-align: center; cursor: pointer; transition: 0.2s; background: white; }
        .picker-card:hover { border-color: var(--primary-blue); box-shadow: 0 2px 8px rgba(21, 101, 192, 0.15); transform: translateY(-2px); }
        .picker-card img { width: 100%; height: 120px; object-fit: contain; margin-bottom: 8px; border-radius: 4px; background: #f9f9f9; }
        .picker-card p { font-size: 0.9rem; font-weight: 600; color: #333; margin: 0; }

        /* Radio Group */
        .radio-group { display: flex; gap: 20px; margin-bottom: 15px; }
        .radio-group label { font-weight: normal; cursor: pointer; display: flex; align-items: center; gap: 5px; }

    </style>
</head>
<body>

    <header class="header">
        <button class="menu-btn" onclick="toggleMenu()">&#9776;</button>
        <div class="header-title">Create Found Owner Report</div>
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
        <a href="AdminFoundOwnerItem.php" style="background-color: var(--light-blue-bg); border-left-color: var(--primary-blue); color: var(--primary-blue);">Found Owner Report</a>
        <a href="ViewUserAccount.php">View User Accounts</a>
        <a href=" AdminHistory.php"> View History</a>
        <a href="AdminContactUS.php">Contact Us</a>
        
        <a href="AdminLogout.php" class="logout-link">Logout</a>
    </nav>

    <div class="overlay" id="overlay" onclick="toggleMenu()"></div>

    <main class="main-content">
        <a href="AdminDashboard.php" class="back-link">← Back to Dashboard</a>

        <div class="form-card">
            <h1>Return Item to Owner</h1>

            <form action="ProcessResolution.php" method="POST" enctype="multipart/form-data" 
                  onkeydown="return event.key != 'Enter';" 
                  onsubmit="return validateForm()">
                
                <div class="form-section">
                    <h3>1. Select Found Item</h3>
                    <button type="button" class="btn-secondary" onclick="openModal()">Browse Active Found Items</button>
                    
                    <div id="selectedItemContainer" class="hidden">
                        <div class="selected-item-preview">
                            <img id="display_item_img" src="">
                            <div style="flex:1;">
                                <strong>Item:</strong> <span id="display_item_name"></span><br>
                                <strong>Location:</strong> Floor <span id="display_floor_level"></span><br>
                                <div style="margin-top:8px;">
                                    <button type="button" class="btn-small-action" id="toggleNoteBtn" onclick="toggleSecretNote()">Show Secret Notes</button>
                                    <div id="display_item_notes" class="hidden secret-note-box"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="found_item_id" id="found_item_id">
                    <input type="hidden" name="floor_level" id="floor_level">
                    <input type="hidden" name="date_found" id="date_found">
                </div>

                <div class="form-section">
                    <h3>2. Owner Information</h3>
                    
                    <label style="color: var(--primary-blue); cursor: pointer; margin-bottom: 15px;">
                        <input type="checkbox" id="is_existing_user" name="is_existing_user" onchange="toggleOwnerType()"> 
                        Owner is a Registered User
                    </label>

                    <div id="user_search_div" class="hidden">
                        <label>Search by Username or Email:</label>
                        <div class="search-container">
                            <input type="text" id="user_search_input" placeholder="Enter search term..." style="margin-bottom:0;"
                                   onkeypress="if(event.key === 'Enter') { event.preventDefault(); searchUser(); }">
                            <button type="button" class="btn-secondary" onclick="searchUser()">Search</button>
                        </div>
                        <div id="user_search_result" style="color: #666; font-style: italic; font-size: 0.9rem;"></div>
                        <input type="hidden" name="selected_user_id" id="selected_user_id">
                    </div>

                    <div id="non_user_form">
                        <label>Full Name:</label>
                        <input type="text" name="owner_name" placeholder="E.g. John Doe">
                        
                        <label>Student/Staff ID:</label>
                        <input type="text" name="owner_sid" placeholder="E.g. 102234">
                        
                        <label>Email Address:</label>
                        <input type="email" name="owner_email" placeholder="email@example.com">
                        
                        <label>Phone Number:</label>
                        <input type="text" name="owner_phone" placeholder="012-3456789">
                        
                        <label>Proof of Identity (ID Card Picture):</label>
                        <input type="file" name="owner_id_pic" accept="image/*">
                    </div>
                </div>

                <div class="form-section" style="border-bottom: none;">
                    <h3>3. Evidence Provided</h3>
                    <div class="radio-group">
                        <label><input type="radio" name="evidence_choice" value="text" checked onclick="toggleEvidence('text')"> Text Description</label>
                        <label><input type="radio" name="evidence_choice" value="image" onclick="toggleEvidence('image')"> Picture Evidence</label>
                    </div>
                    
                    <div id="evidence_text_div">
                        <textarea name="evidence_desc" rows="3" placeholder="Describe the proof provided (e.g., knew the password, described specific scratches)..."></textarea>
                    </div>
                    
                    <div id="evidence_image_div" class="hidden">
                        <label>Upload Evidence Picture:</label>
                        <input type="file" name="evidence_file" accept="image/*">
                    </div>
                </div>

                <input type="submit" value="Complete Return Process" class="btn-primary">
            </form>
        </div>
    </main>

    <div id="itemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Select Found Item</h2>
                <span style="cursor:pointer; font-size:28px;" onclick="closeModal()">&times;</span>
            </div>
            
            <div class="item-grid">
                <?php if($found_items_result->num_rows > 0): ?>
                    <?php while($item = $found_items_result->fetch_assoc()): ?>
                        <div class="picker-card" onclick="selectItem('<?php echo $item['id']; ?>', '<?php echo addslashes($item['item_name']); ?>', '<?php echo $item['picture_path']; ?>', '<?php echo addslashes($item['secret_notes']); ?>', '<?php echo $item['floor_level']; ?>', '<?php echo $item['created_at']; ?>')">
                            <img src="<?php echo $item['picture_path']; ?>" alt="Item Image">
                            <p><?php echo htmlspecialchars($item['item_name']); ?></p>
                            <small style="color:#777;">Floor: <?php echo htmlspecialchars($item['floor_level']); ?></small>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align:center; width:100%; color:#666;">No active found items available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // --- Sidebar Logic ---
        function toggleMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }

        // --- Modal Logic ---
        function openModal() { document.getElementById('itemModal').style.display = 'block'; }
        function closeModal() { document.getElementById('itemModal').style.display = 'none'; }
        
        // Close modal if clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('itemModal')) {
                closeModal();
            }
        }

        // --- Item Selection ---
        function selectItem(id, name, img, notes, floor, date) {
            document.getElementById('found_item_id').value = id;
            document.getElementById('floor_level').value = floor; // Pass floor
            document.getElementById('date_found').value = date;   // Pass date
            
            document.getElementById('display_item_name').innerText = name;
            document.getElementById('display_floor_level').innerText = floor;
            document.getElementById('display_item_notes').innerText = notes;
            document.getElementById('display_item_img').src = img;
            
            // Hide note initially
            document.getElementById('display_item_notes').classList.add('hidden');
            document.getElementById('toggleNoteBtn').innerText = "Show Secret Notes";
            
            document.getElementById('selectedItemContainer').classList.remove('hidden');
            closeModal();
        }

        function toggleSecretNote() {
            const div = document.getElementById('display_item_notes');
            const btn = document.getElementById('toggleNoteBtn');
            if(div.classList.contains('hidden')) {
                div.classList.remove('hidden');
                btn.innerText = "Hide Notes";
            } else {
                div.classList.add('hidden');
                btn.innerText = "Show Notes";
            }
        }

        // --- Owner Search ---
        function toggleOwnerType() {
            const isUser = document.getElementById('is_existing_user').checked;
            document.getElementById('user_search_div').classList.toggle('hidden', !isUser);
            document.getElementById('non_user_form').classList.toggle('hidden', isUser);
        }

        function searchUser() {
            let query = document.getElementById('user_search_input').value;
            if(!query) return;
            
            let resultDiv = document.getElementById('user_search_result');
            resultDiv.innerHTML = "Searching...";

            fetch('SearchUser.php?query=' + query)
                .then(res => res.json())
                .then(data => {
                    resultDiv.innerHTML = "";
                    if(data.length > 0) {
                        data.forEach(user => {
                            let item = document.createElement('div');
                            item.className = "user-result-item";
                            item.innerHTML = `<strong>${user.username}</strong> <br> <small>${user.email}</small>`;
                            item.onclick = () => {
                                document.getElementById('selected_user_id').value = user.id;
                                resultDiv.innerHTML = `<div style="background:#e8f5e9; padding:10px; border-radius:4px; border:1px solid #c8e6c9; color:#2e7d32;">
                                    <strong>Selected:</strong> ${user.username} (${user.email})
                                </div>`;
                            };
                            resultDiv.appendChild(item);
                        });
                    } else { 
                        resultDiv.innerHTML = "<p style='color:#d32f2f;'>No user found.</p>"; 
                    }
                })
                .catch(err => {
                    resultDiv.innerHTML = "<p style='color:#d32f2f;'>Error searching.</p>";
                });
        }

        // --- Evidence Toggle ---
        function toggleEvidence(type) {
            document.getElementById('evidence_text_div').classList.toggle('hidden', type !== 'text');
            document.getElementById('evidence_image_div').classList.toggle('hidden', type !== 'image');
        }

        // --- Validation ---
        function validateForm() {
            if(!document.getElementById('found_item_id').value) {
                alert("You must select a found item first!");
                return false;
            }

            const isUser = document.getElementById('is_existing_user').checked;
            if(isUser) {
                if(!document.getElementById('selected_user_id').value) {
                    alert("Please search and click a user to select them.");
                    return false;
                }
            } else {
                const name = document.getElementsByName('owner_name')[0].value;
                if(name.trim() == "") {
                    alert("Please fill in the owner's name.");
                    return false;
                }
            }
            return true;
        }
    </script>
</body>
</html>
<?php include '../footer.html'; ?>