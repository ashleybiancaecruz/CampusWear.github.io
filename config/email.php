<?php

function sendEmailViaGoogleCloud($to, $subject, $body, $isHtml = true) {
    $vendorPath = __DIR__ . '/../vendor/autoload.php';
    
    if (!file_exists($vendorPath)) {
        return sendEmailFallback($to, $subject, $body, $isHtml);
    }
    
    require_once $vendorPath;
    
    if (!class_exists('Google\Client')) {
        return sendEmailFallback($to, $subject, $body, $isHtml);
    }
    
    try {
        $client = new \Google\Client();
        $client->setClientId(getenv('GOOGLE_CLIENT_ID') ?: '');
        $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET') ?: '');
        $client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI') ?: '');
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/gmail.send']);
        
        $accessToken = getenv('GOOGLE_ACCESS_TOKEN') ?: '';
        if (empty($accessToken)) {
            return sendEmailFallback($to, $subject, $body, $isHtml);
        }
        
        $client->setAccessToken($accessToken);
        
        if ($client->isAccessTokenExpired()) {
            $refreshToken = getenv('GOOGLE_REFRESH_TOKEN') ?: '';
            if (!empty($refreshToken)) {
                $client->refreshToken($refreshToken);
                $newToken = $client->getAccessToken();
                if (isset($newToken['access_token'])) {
                    putenv('GOOGLE_ACCESS_TOKEN=' . $newToken['access_token']);
                }
            } else {
                return sendEmailFallback($to, $subject, $body, $isHtml);
            }
        }
        
        $gmailService = new \Google\Service\Gmail($client);
        
        $message = new \Google\Service\Gmail\Message();
        $rawMessage = "To: $to\r\n";
        $rawMessage .= "Subject: $subject\r\n";
        $rawMessage .= "Content-Type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
        $rawMessage .= "\r\n";
        $rawMessage .= $body;
        
        $message->setRaw(base64_encode($rawMessage));
        $gmailService->users_messages->send('me', $message);
        
        return true;
    } catch (Exception $e) {
        return sendEmailFallback($to, $subject, $body, $isHtml);
    }
}

function sendEmailFallback($to, $subject, $body, $isHtml = true) {
    $headers = "From: CampusWear <noreply@campuswear.com>\r\n";
    $headers .= "Reply-To: noreply@campuswear.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: " . ($isHtml ? "text/html" : "text/plain") . "; charset=UTF-8\r\n";
    
    return mail($to, $subject, $body, $headers);
}

function sendOrderConfirmationEmail($to, $orderDetails) {
    if (empty($to)) {
        return false;
    }
    
    $subject = 'Order Confirmation - CampusWear';
    $body = '<html><body>';
    $body .= '<h2>Thank you for your order</h2>';
    $body .= '<p>Your order has been confirmed and will be processed shortly.</p>';
    $body .= '<h3>Order Details:</h3>';
    $body .= '<ul>';
    foreach ($orderDetails['items'] as $item) {
        $body .= '<li>' . htmlspecialchars($item['name']) . ' - Quantity: ' . $item['quantity'] . ' - ₱' . number_format($item['price'], 2) . '</li>';
    }
    $body .= '</ul>';
    $body .= '<p><strong>Total Amount: ₱' . number_format($orderDetails['total'], 2) . '</strong></p>';
    $body .= '<p>Order ID: ' . htmlspecialchars($orderDetails['order_id']) . '</p>';
    $body .= '<p>We will notify you once your order is ready for pickup.</p>';
    $body .= '</body></html>';
    
    return sendEmailViaGoogleCloud($to, $subject, $body, true);
}

function sendApplicationConfirmationEmail($to, $applicationDetails) {
    if (empty($to)) {
        return false;
    }
    
    $isGoogleAccount = (
        strpos(strtolower($to), '@gmail.com') !== false || 
        strpos(strtolower($to), '@googlemail.com') !== false
    );
    
    $subject = 'ISC Membership Application Received - CampusWear';
    $body = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
    $body .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #8B0000; border-radius: 10px;">';
    $body .= '<h2 style="color: #8B0000;">Thank you for your ISC Membership Application!</h2>';
    $body .= '<p>Dear ' . htmlspecialchars($applicationDetails['full_name']) . ',</p>';
    $body .= '<p>We have successfully received your application for ISC membership. Your application is currently under review.</p>';
    $body .= '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">';
    $body .= '<p><strong>Application ID:</strong> #' . htmlspecialchars($applicationDetails['application_id']) . '</p>';
    $body .= '<p><strong>Submitted:</strong> ' . htmlspecialchars($applicationDetails['submitted_at']) . '</p>';
    $body .= '</div>';
    $body .= '<p><strong>What happens next?</strong></p>';
    $body .= '<ol>';
    $body .= '<li>Your application will be reviewed by ISC officers</li>';
    $body .= '<li>You may be contacted for an interview</li>';
    $body .= '<li>You\'ll receive an email notification once your application is processed</li>';
    $body .= '<li>Upon approval, you\'ll automatically receive ISC member discounts</li>';
    $body .= '</ol>';
    $body .= '<p>If you have any questions, please don\'t hesitate to contact us.</p>';
    $body .= '<p>Best regards,<br><strong>CampusWear Team</strong></p>';
    $body .= '</div>';
    $body .= '</body></html>';
    
    if ($isGoogleAccount) {
        return sendEmailViaPHPMailer($to, $subject, $body);
    } else {
        return sendEmailViaGoogleCloud($to, $subject, $body, true);
    }
}

function sendEmailViaPHPMailer($to, $subject, $body) {
    $vendorPath = __DIR__ . '/../vendor/autoload.php';
    
    if (!file_exists($vendorPath)) {
        error_log("Email: Vendor autoload not found, using fallback");
        return sendEmailFallback($to, $subject, $body, true);
    }
    
    require_once $vendorPath;
    
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("Email: PHPMailer class not found, using fallback");
        return sendEmailFallback($to, $subject, $body, true);
    }
    
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = getenv('SMTP_USERNAME') ?: 'ashleybiancacruz@gmail.com';
        $mail->Password = getenv('SMTP_PASSWORD') ?: 'qzcjsejcmwqhjezg';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = getenv('SMTP_PORT') ?: 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: 'ashleybiancacruz@gmail.com', getenv('SMTP_FROM_NAME') ?: 'CampusWear Team');
        $mail->addAddress($to);
        $mail->addReplyTo(getenv('SMTP_FROM_EMAIL') ?: 'ashleybiancacruz@gmail.com', 'CampusWear Support');
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        if (!$mail->send()) {
            error_log("Email send failed: " . $mail->ErrorInfo);
            return sendEmailFallback($to, $subject, $body, true);
        }
        
        error_log("Email sent successfully to: $to");
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        return sendEmailFallback($to, $subject, $body, true);
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return sendEmailFallback($to, $subject, $body, true);
    }
}
?>
