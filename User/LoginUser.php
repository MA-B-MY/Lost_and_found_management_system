<?php
session_start();
// Include the shared database configuration
include "UserDBconfig.php"; // Adjust filename if you renamed it

// Check if admin is already logged in (optional check)

// Initialize messages
$message = "";

// Check for status messages from the registration process or login attempts
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'registration_success') {
        $message = "<p style='color: green; font-weight: bold;'>Registration successful! Please log in.</p>";
    } elseif ($_GET['status'] == 'logged_out') {
        $message = "<p style='color: blue;'>You have been logged out.</p>";
    }
} elseif (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_credentials') {
        $message = "<p style='color: red; font-weight: bold;'>Error: Invalid username or password.</p>";
    } elseif ($_GET['error'] == 'missing_fields') {
        $message = "<p style='color: red; font-weight: bold;'>Error: Please enter both username and password.</p>";
    }
}
?>


<!DOCTYPE html> <html>
<head>
    <title>User Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- GLOBAL STYLES (Matching Dashboard) --- */
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
            height: 100vh;
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

        /* No menu button styling here, as requested */

        .header-title {
            font-size: 1.2rem;
            font-weight: 500;
            /* Centered title for login page looks nice, or left aligned */
            margin-left: 0; 
        }

        /* --- LOGIN CONTAINER --- */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 60px; /* Push down below fixed header */
        }

        .login-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1); /* Slightly stronger shadow than dashboard items */
            width: 100%;
            max-width: 400px;

        }

        .login-card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .divider {
            border: 0;
            border-top: 1px solid #eee;
            margin: 20px 0;
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

        .form-group input {
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
            background-color: #00332a; /* Slightly darker green */
        }

        /* --- LINKS --- */
        .auth-links {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9em;
            color: #666;
        }

        .auth-links a {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }
        
        .error-msg {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-title">Lost and Found System</div>
    </header>

    <main class="main-content">
        <div class="login-card">
            <h2>User Login</h2>
            
            <?php if (!empty($message)): ?>
                <div class="error-msg"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="UserLoginProcess.php" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Enter your password">
                </div>

                <button type="submit" name="login_button" class="submit-btn">Login</button>
            </form>
            
            <hr class="divider">
            
            <div class="auth-links">
                <p>Don't have an account? <a href="RegisterUser.php">Register Here</a></p>
                <br>
                <p><a href="UserForgotPassword.php">Forgot Password?</a></p>
            </div>

        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>