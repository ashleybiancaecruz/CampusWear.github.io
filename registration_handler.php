<?php
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$user_email_from_form = $_POST['email'] ?? 'your_own_email@gmail.com'; 
$user_name_from_form  = $_POST['fullname'] ?? 'Test User';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ashleybiancacruz@gmail.com'; 
    $mail->Password   = 'qzcjsejcmwqhjezg';           
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

   
    $mail->setFrom('ashleybiancacruz@gmail.com', 'CampusWear Shop');
    $mail->addAddress($user_email_from_form, $user_name_from_form); 

    $mail->isHTML(true);
    $mail->Subject = "Welcome to CampusWear, " . $user_name_from_form;
    $mail->Body    = "
        <h1>Account Confirmed!</h1>
        <p>Hello <strong>{$user_name_from_form}</strong>,</p>
        <p>Thank you for using your Google account (<em>{$user_email_from_form}</em>) to join CampusWear.</p>
        <p>Your shopping account is now active. No further action is required from your side.</p>
        <br>
        <p>Best regards,<br>The CampusWear Automation System</p>";

    $mail->send();
    echo "Success: Welcome email delivered to " . htmlspecialchars($user_email_from_form);

} catch (Exception $e) {
    error_log("Email failed for {$user_email_from_form}: " . $mail->ErrorInfo);
}

?>