<?php 
// Include configuration file 
require_once 'UserForgotPasswordConfig.php'; // Corrected file name
 
// Check if OTP is verified and email is set 
// This check assumes UserVerifyOTP.php sets $_SESSION['otp_verified'] = true;
if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) { 
    header("Location: UserForgetpassword.php"); // Corrected redirection
    exit(); 
} 
 
// Handle form submission 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $password = $_POST['password']; 
    $confirm_password = $_POST['confirm_password']; 
     
    if ($password !== $confirm_password) { 
        $error = "Passwords do not match!"; 
    } else { 
        $email = $_SESSION['reset_email']; 
        // Hash the new password 
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); 
         
        // Update password in database (Updated table and column names)
        $stmt = $conn->prepare("UPDATE UserData SET password_hash = ? WHERE email = ?"); 
$stmt->bind_param("ss", $password, $email);

        if ($stmt->execute()) { 
            // Clear all session variables related to the reset process
            session_unset(); 
            session_destroy(); 
             
            // Redirect to login page with success message
            header("Location: LoginUser.php?reset=success"); // Corrected redirection
            exit(); 
        } else { 
            $error = "Error updating password. Please try again. Database Error: " . $stmt->error;
        } 
    } 
} 
?>
 
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* --- GLOBAL STYLES (Matching LoginUser) --- */
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

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 60px;
        }

        .login-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }

        .login-card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

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
            background-color: #00332a;
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
            <h2>Reset Password</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required placeholder="">
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required placeholder="Repeat password">
                </div>

                <button type="submit" class="submit-btn">Update Password</button>
            </form>
        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>