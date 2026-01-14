<?php
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$statusMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userEmail = $_POST['email']; 
    $userName  = $_POST['fullname'];

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();                                           
        $mail->Host       = 'smtp.gmail.com';                      
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = 'ashleybiancacruz@gmail.com';           
        $mail->Password   = 'qzcjsejcmwqhjezg';                     
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
        $mail->Port       = 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom('ashleybiancacruz@gmail.com', 'CampusWear Official');
        $mail->addAddress($userEmail, $userName);                 

        $mail->isHTML(true);                                        
        $mail->Subject = "Welcome to CampusWear, $userName!";
        $mail->Body    = "
            <div style='font-family: sans-serif; padding: 20px; border: 1px solid #eee;'>
                <h1 style='color: #2c3e50;'>Welcome to CampusWear!</h1>
                <p>Hello <strong>$userName</strong>,</p>
                <p>We are excited to confirm that you have used your Google account (<em>$userEmail</em>) to join our shopping community.</p>
                <p>No further action is required. You can start shopping immediately!</p>
                <br>
                <p>Best regards,<br><strong>CampusWear Automation System</strong></p>
            </div>";

        $mail->send();
        $statusMessage = "<p style='color: green;'>Success! Welcome email sent to $userEmail.</p>";
    } catch (Exception $e) {
        $statusMessage = "<p style='color: red;'>Error: Email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Join CampusWear</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 350px; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #2c3e50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #34495e; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>CampusWear Signup</h2>
    <?php echo $statusMessage; ?>
    <form action="" method="POST">
        <label>Full Name</label>
        <input type="text" name="fullname" placeholder="John Doe" required>
        
        <label>Gmail Address</label>
        <input type="email" name="email" placeholder="example@gmail.com" required>
        
        <button type="submit">Create Shopping Account</button>
    </form>
</div>

</body>
</html>