<?php

session_start(); 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'EmailConfig.php'; 

$message = "Unknown error occurred.";
$status = "error";

$active_table = "userdata";
$archive_table = "deleted_userdata";
$reports_table = "missingitems"; 


$admin_id = $_SESSION['AdminID'];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ViewUserAccount.php?status=error&msg=Invalid ID");
    exit();
}
$user_id = (int)$_GET['id'];

if (isset($conn) && $conn->connect_error === null) {
    $conn->begin_transaction();

    try {
        // 1. Fetch User Data
        $stmt_select = $conn->prepare("SELECT * FROM $active_table WHERE id = ?");
        $stmt_select->bind_param("i", $user_id);
        $stmt_select->execute();
        $user_data = $stmt_select->get_result()->fetch_assoc();
        
        if (!$user_data) throw new Exception("User not found.");
        $stmt_select->close();

        // 2. Archive User
        $sql_insert = "INSERT INTO $archive_table (id, email, username, phoneUser, id_number, choice, user_image_path1, user_image_path2, password_hash, created_at, deleted_by_admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issssissssi", $user_data['id'], $user_data['email'], $user_data['username'], $user_data['phoneUser'], $user_data['id_number'], $user_data['choice'], $user_data['user_image_path1'], $user_data['user_image_path2'], $user_data['password_hash'], $user_data['created_at'], $admin_id);
        $stmt_insert->execute();
        $stmt_insert->close();

        // 3. DELETE FROM missingitems FIRST (Fixes Constraint Error)
        $stmt_rep = $conn->prepare("DELETE FROM $reports_table WHERE user_id = ?");
        $stmt_rep->bind_param("i", $user_id);
        $stmt_rep->execute();
        $stmt_rep->close();

        // 4. DELETE from Active Table
        $stmt_delete = $conn->prepare("DELETE FROM $active_table WHERE id = ?");
        $stmt_delete->bind_param("i", $user_id);
        $stmt_delete->execute();

        if ($stmt_delete->affected_rows === 0) {
            throw new Exception("Failed to delete user account.");
        }
        $stmt_delete->close();
        
        $conn->commit();
        sendDeletionEmail($user_data['email'], $user_data['username']);

        $status = "success";
        $message = "User and all related reports deleted successfully.";

    } catch (Exception $e) {
        $conn->rollback();
        $status = "error";
        $message = $e->getMessage();
    }
    $conn->close();
}

header("Location: ViewUserAccount.php?status=$status&msg=" . urlencode($message));
exit();

function sendDeletionEmail($recipient_email, $recipient_username) {
    global $smtpHost, $smtpUsername, $smtpPassword, $smtpPort, $smtpFromEmail, $smtpFromName;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port = $smtpPort;
        $mail->setFrom($smtpFromEmail, $smtpFromName);
        $mail->addAddress($recipient_email, $recipient_username);
        $mail->isHTML(true);
        $mail->Subject = 'Account Deletion Notification';
        $mail->Body = "Your account have and the associated reports have been deleted because you  violating our Community Guidelines. If You belive this is an error, please contact the admin";
        $mail->send();
        return true;
    } catch (Exception $e) { return false; }
}
?>