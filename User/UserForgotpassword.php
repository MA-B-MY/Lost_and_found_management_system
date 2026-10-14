<?php
session_start();

// Use PHPMailer classes
require_once 'UserForgotPasswordConfig.php'; // your config file
require '../vendor/autoload.php'; // Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the required SMTP variables are defined
if (!isset($smtpHost, $smtpUsername, $smtpPassword, $smtpPort, $smtpFromEmail, $smtpFromName)) {
    // Safety check since the config variables were commented out previously.
    $error = "Email configuration missing. Please check UserForgotPasswordConfig.php.";
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email exists in database (Updated table name to UserData)
        $stmt = $conn->prepare("SELECT id FROM UserData WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            // Generate OTP and store in session
            $otp = random_int(100000, 999999); // cryptographically safer
            // Note: Storing OTP in a temporary table would be more secure than a session.
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_email'] = $email;
            $_SESSION['otp_time'] = time();

            // Create a new PHPMailer instance
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = $smtpHost;
                $mail->SMTPAuth = true;
                $mail->Username = $smtpUsername;
                $mail->Password = $smtpPassword;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $smtpPort;

                // Recipients
                $mail->setFrom($smtpFromEmail, $smtpFromName);
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "Your OTP for password reset is: <b>{$otp}</b><br>This OTP will expire in 5 minutes.";

                if ($mail->send()) {
                    $_SESSION['status'] = "An OTP has been sent to your email address. Please check your inbox (and spam).";
                    header("Location: UserVerifyOTP.php");
                    exit();
                } else {
                    $error = "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
                }
            } catch (Exception $e) {
                $error = "Message could not be sent. Mailer Exception: " . $e->getMessage();
            }
        } else {
            $error = "Email address not found!";
        }

        $stmt->close();
    }
}
?>
<html>
<head>
    <title>Forgot Password</title>
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

        .form-group input[type="email"] {
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
        }

        .submit-btn:hover {
            background-color: #00332a;
        }

        /* --- MESSAGES & LINKS --- */
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
            <h2>Forgot Password</h2>

            <?php if (isset($error)) { ?>
                <div class="error-box"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php } ?>

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="Enter your registered email">
                </div>

                <button type="submit" class="submit-btn">Send OTP</button>

                <div class="links">
                    <a href="LoginUser.php" class="back-link">Back to Login</a>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
<?php include '../footer.html'; ?>