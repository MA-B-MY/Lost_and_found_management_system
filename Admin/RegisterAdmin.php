<?php
// Initialize error variable
$error_message = "";

if (isset($_GET['error'])) {
    $error_code = $_GET['error'];
    
    // Map error codes to user-friendly messages
    if ($error_code == 'invalid_access') {
        $error_message = "Error: Invalid access method.";
    } elseif ($error_code == 'db_fail') {
        $error_message = "Error: Database connection failed.";
    } elseif ($error_code == 'missing_fields') {
        $error_message = "Error: Please fill in all required fields.";
    } elseif ($error_code == 'invalid_file_type') {
        $error_message = "Error: Invalid picture file type. Only JPG, JPEG, PNG, or GIF are allowed.";
    } elseif ($error_code == 'upload_failed') {
        $error_message = "Error: Failed to upload picture.";
    } elseif ($error_code == 'already_exists') {
        $error_message = "Error: Username, Email, or ID Number is already registered.";
    } elseif ($error_code == 'database_insert_failed') {
        $error_message = "Error: Registration failed due to a database error.";
    } else {
        $error_message = "An unknown error occurred.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
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
            min-height: 100vh; /* Allow scroll if form is tall */
            display: flex;
            flex-direction: column;
        }

        /* Fixed Header */
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

        /* --- 2. CARD STYLES --- */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin-top: 60px; /* Offset for header */
        }

        .auth-card {
            background: white;
            padding: 40px;
            width: 100%;
            max-width: 500px; /* Slightly wider for registration forms */
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-align: center;
            margin: 20px 0; /* Add margin for mobile scrolling */
        }

        .auth-card h1 {
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

        /* Combined selector for all input types */
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            transition: border 0.3s;
            background: #fff;
        }

        /* Special styling adjustment for file input to look cleaner */
        input[type="file"] {
            padding: 9px; /* Adjustment to align visually */
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus,
        input[type="file"]:focus {
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
            margin-top: 10px;
        }

        input[type="submit"]:hover {
            background-color: var(--primary-dark);
        }

        /* Links */
        .login-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        .login-link:hover { text-decoration: underline; }

        /* Error Message Box */
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="header-title">Lost & Found Admin Portal</div>
    </header>

    <main class="main-content">
        <div class="auth-card">
            <h1>Admin Registration</h1>

            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="RegisterAdminProcess.php" method="POST" enctype="multipart/form-data">
                
                <label for="AdminName">Username</label>
                <input type="text" id="AdminName" name="AdminName" placeholder="Create a username" required>

                <label for="AdminEmail">Email</label>
                <input type="email" id="AdminEmail" name="AdminEmail" placeholder="Enter your email" required>

                <label for="AdminID">ID Number</label>
                <input type="text" id="AdminID" name="AdminID" placeholder="Enter your Staff/Admin ID" required>

                <label for="AdminPassword">Password</label>
                <input type="password" id="AdminPassword" name="AdminPassword" placeholder="Create a strong password" required>
                
                <label for="AdminPicture">Profile Picture</label>
                <input type="file" id="AdminPicture" name="AdminPicture" accept="image/*" required>

                <input type="submit" value="Register Admin">

            </form>

            <a href="LoginAdmin.php" class="login-link">← Back to Login</a>
        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>