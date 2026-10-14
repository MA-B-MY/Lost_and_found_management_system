<?php
session_start();
// Include the shared database configuration
include "AdminDBconfig.php"; // Adjust filename if you renamed it

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

<!DOCTYPE html><html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        /* --- 1. SHARED STYLES --- */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        :root {
            /* DARK BLUE Theme */
            --primary-blue: #1565c0; /* Blue 800 */
            --primary-dark: #0d47a1; /* Blue 900 */
        }

        body {
            background-color: #f4f6f8;
            color: #333;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Fixed Header (No Menu Button for Login) */
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

        .header-title { font-size: 1.2rem; font-weight: 500; }

        /* --- 2. LOGIN CARD STYLES --- */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center; /* Center Vertically */
            justify-content: center; /* Center Horizontally */
            padding: 20px;
            margin-top: 60px; /* Offset for header */
        }

        .login-card {
            background: white;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .login-card h1 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        /* Form Elements */
        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 4px rgba(21, 101, 192, 0.2);
        }

        input[type="submit"] {
            width: 100%;
            background-color: var(--primary-blue);
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
            background-color: var(--primary-dark);
        }

        /* Links */
        .register-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .register-link:hover { text-decoration: underline; }

        /* Error Message Box */
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-title">Lost & Found Admin Portal</div>
    </header>

    <main class="main-content">
        <div class="login-card">
            <h1>Admin Login</h1>

            <?php if (!empty($message)): ?>
                <div class="error-message">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form action="LoginAdminProcess.php" method="POST">
                
                <label for="AdminName">Username or Email</label>
                <input type="text" id="AdminName" name="AdminName" placeholder="Enter your ID or Email" required>

                <label for="AdminPassword">Password</label>
                <input type="password" id="AdminPassword" name="AdminPassword" placeholder="Enter your password" required>
                
                <input type="submit" value="Login">
                
            </form>

            <a href="RegisterAdmin.php" class="register-link">← Register New Admin</a><br><br>
            <a>If admin forgot password, please contact customer service <br>
                Number : +60 1234 5799 <br>
            Email : HelpDeskCompanyA@gmail.com </a>
        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>