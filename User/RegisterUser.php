
<!DOCTYPE html> 
<html>
<head>
    <title>User Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- GLOBAL STYLES --- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            --primary-green: #004d40;
        }

        body {
            background-color: #f4f6f8;
            min-height: 100vh; /* Allow scrolling if form is long */
            display: flex;
            flex-direction: column;
        }

        /* --- HEADER --- */
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

        .header-title {
            font-size: 1.2rem;
            font-weight: 500;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Aligns to top so we can scroll */
            padding: 20px;
            margin-top: 80px; /* Push down below fixed header + extra space */
            margin-bottom: 40px;
        }

        .register-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px; /* Slightly wider than login for more data */
            border-top: 5px solid var(--primary-green);
        }

        .register-card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* --- FORM ELEMENTS --- */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="tel"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            border-color: var(--primary-green);
            outline: none;
        }

        /* Radio Button Section */
        .radio-group {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #eee;
            margin-bottom: 15px;
        }

        .radio-options {
            display: flex;
            gap: 20px;
            margin-top: 5px;
        }

        .radio-options label {
            font-weight: normal;
            cursor: pointer;
        }

        /* File Upload Section */
        .file-group {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px dashed #ccc;
            border-radius: 4px;
            background-color: #fafafa;
        }
        
        .file-group input[type="file"] {
            font-size: 14px;
            width: 100%;
        }

        /* Button */
        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-green);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #00332a;
        }

        .divider {
            border: 0;
            border-top: 1px solid #eee;
            margin: 20px 0;
        }

        /* Error Box Style */
        .error-box {
            color: #d32f2f;
            background-color: #fdecea;
            border: 1px solid #d32f2f;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .back-link {
            text-align: center;
            display: block;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .back-link:hover { color: var(--primary-green); }

    </style>
</head>
<body>

    <header class="header">
        <div class="header-title">Lost and Found System</div>
    </header>

    <main class="main-content">
        <div class="register-card">
            <h2>User Registration</h2>
            
            <?php
            // --- Error Display Block ---
            if (isset($_GET['error'])) { 
                $error = $_GET['error'];
                $message = '';
                
                if ($error === 'email_used') {
                    $message = 'Error: The email address is already registered.';
                } elseif ($error === 'id_used') {
                    $message = 'Error: The ID Number is already registered.';
                } elseif ($error === 'phone_used') {
                    $message = 'Error: The phone number is already registered.';
                }

                if ($message) {
                    echo '<div class="error-box">' . htmlspecialchars($message) . '</div>';
                }
            }
            ?>

            <form action="UserRegisterProcess.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="emailuser" required placeholder="example@unikl.edu.my">
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Enter your full name">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phoneUser" required placeholder="012-3456789">
                </div>

                <div class="form-group">
                    <label for="user_id">ID Number (Student/Staff ID)</label>
                    <input type="text" id="user_id" name="id_number" required placeholder="ID Number">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a password">
                </div>

                <div class="form-group radio-group">
                    <label>Role</label>
                    <div class="radio-options">
                        <div>
                            <input type="radio" id="option1" name="choice" value="1" required>
                            <label for="option1">UniKL Student</label>
                        </div>
                        <div>
                            <input type="radio" id="option2" name="choice" value="2" required>
                            <label for="option2">Staff</label>
                        </div>
                    </div>
                </div>

                <p style="margin-bottom: 10px; font-weight: 500; color: #333;">Upload Required Documents</p>
                
                <div class="form-group file-group">
                    <label for="user_pic">Profile Picture</label>
                    <input type="file" id="user_pic" name="user_image_1" accept="image/*" required>
                </div>

                <div class="form-group file-group">
                    <label for="student_id_upload">Student/Staff ID Card Image</label>
                    <input type="file" id="student_id_upload" name="user_image_2" accept="image/*" required>
                </div>

                <button type="submit" name="register_button" class="submit-btn">Register</button>
                
                <a href="LoginUser.php" class="back-link">Already have an account? Login here</a>

            </form>
        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>