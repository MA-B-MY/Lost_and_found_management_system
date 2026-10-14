<?php 
// Include configuration file 
require_once 'UserForgotPasswordConfig.php'; // Corrected file name
 
// Check if OTP and email are set in session (i.e., if UserForgetpassword.php ran successfully)
if (!isset($_SESSION['reset_otp']) || !isset($_SESSION['reset_email'])) { 
    header("Location: UserForgetpassword.php"); // Corrected redirection
    exit(); 
} 
 
// Capture and clear any status message set by previous step 
$status = null; 
if (isset($_SESSION['status'])) { 
    $status = $_SESSION['status']; 
    unset($_SESSION['status']); 
} 
 
// Handle form submission 
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $entered_otp = $_POST['otp']; 
    $stored_otp = $_SESSION['reset_otp']; 
    $otp_time = $_SESSION['otp_time']; 
     
    // Check if OTP is expired (600 seconds = 10 minutes validity) 
    if (time() - $otp_time > 60) { 
        $error = "OTP has expired. Please request a new one."; 
        // Clear expired session data
        unset($_SESSION['reset_otp']); 
        unset($_SESSION['otp_time']); 
    } 
    // Verify OTP 
    else if ($entered_otp == $stored_otp) { 
        // Set flag and redirect to password reset page
        $_SESSION['otp_verified'] = true; 
        header("Location: UserResetPassword.php"); // Corrected redirection
        exit(); 
    } else { 
        $error = "Invalid OTP. Please try again."; 
    } 
} 

?>
 
<html>
<head>
    <title>Verify OTP</title>
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

        .header-title {
            font-size: 1.2rem;
            font-weight: 500;
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            margin-top: 60px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            border-top: 5px solid var(--primary-green);
        }

        .card h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        /* --- FORM ELEMENTS --- */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s;
            /* Hide spin buttons for number input if desired, mostly for aesthetics */
            -moz-appearance: textfield; 
        }
        
        /* Chrome, Safari, Edge, Opera - hide arrows on number input */
        .form-group input[type="number"]::-webkit-outer-spin-button,
        .form-group input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
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
        }

        .submit-btn:hover {
            background-color: #00332a;
        }

        /* --- ALERTS --- */
        .error-box {
            color: #d32f2f;
            background-color: #fdecea;
            border: 1px solid #d32f2f;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }

        .success-box {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }

        /* --- LINKS --- */
        .back-link {
            text-align: center;
            display: block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .back-link:hover { color: var(--primary-green); }

    </style>
</head>
<body>

    <header class="header">
        <div class="header-title">Lost and Found System</div>
    </header>

    <main class="main-content">
        <div class="card">
            <h2>Verify OTP</h2>

            <?php if (isset($error)) { ?>
                <div class="error-box"><?php echo $error; ?></div>
            <?php } ?>

            <?php if (isset($status)) { ?>
                <div class="success-box"><?php echo $status; ?></div>
            <?php } ?>

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="otp">Enter OTP</label>
                    <input type="number" name="otp" id="otp" required placeholder="Enter the code sent to your email">
                </div>

                <button type="submit" class="submit-btn">Verify OTP</button>

                <div class="links">
                    <a href="UserForgotPassword.php" class="back-link">Request New OTP</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>