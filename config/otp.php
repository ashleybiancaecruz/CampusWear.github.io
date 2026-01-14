<?php
ob_clean();
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
function generateOTP($length = 6) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendOTPviaSMS($phone, $otp) {
    $apiKey = getenv('SMS_API_KEY') ?: '';
    $apiUrl = getenv('SMS_API_URL') ?: 'https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json';
    
    if (empty($apiKey) || empty($apiUrl)) {
        return false;
    }
    
    $message = "Your CampusWear verification code is: $otp. Valid for 10 minutes.";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'From' => getenv('SMS_FROM_NUMBER') ?: '',
        'To' => $phone,
        'Body' => $message
    ]));
    curl_setopt($ch, CURLOPT_USERPWD, $apiKey . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode >= 200 && $httpCode < 300;
}

function verifyOTP($phone, $otp, $conn) {
    $phone = $conn->real_escape_string($phone);
    $otp = $conn->real_escape_string($otp);
    
    $conn->query("CREATE TABLE IF NOT EXISTS otp_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        verified BOOLEAN DEFAULT FALSE,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_phone_otp (phone, otp),
        INDEX idx_expires (expires_at)
    )");
    
    $result = $conn->query("SELECT * FROM otp_verifications 
                           WHERE phone = '$phone' AND otp = '$otp' AND verified = FALSE 
                           AND expires_at > NOW() 
                           ORDER BY created_at DESC LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $conn->query("UPDATE otp_verifications SET verified = TRUE WHERE phone = '$phone' AND otp = '$otp'");
        return true;
    }
    
    return false;
}

function storeOTP($phone, $otp, $conn) {
    $phone = $conn->real_escape_string($phone);
    $otp = $conn->real_escape_string($otp);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    $conn->query("CREATE TABLE IF NOT EXISTS otp_verifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        phone VARCHAR(20) NOT NULL,
        otp VARCHAR(10) NOT NULL,
        verified BOOLEAN DEFAULT FALSE,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_phone_otp (phone, otp),
        INDEX idx_expires (expires_at)
    )");
    
    $conn->query("INSERT INTO otp_verifications (phone, otp, expires_at) 
                  VALUES ('$phone', '$otp', '$expiresAt')");
    
    $conn->query("DELETE FROM otp_verifications WHERE expires_at < NOW()");
}
?>
