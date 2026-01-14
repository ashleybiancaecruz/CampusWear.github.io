<?php
session_start();
header('Content-Type: application/json');

$api_token = '2ad954c7e94d4e0122edc7b460444ea8'; 
$email = 'ashleybiancacruz@gmail.com'; 
$password = 'EWjFZ.JzEzFR5cv'; 
$device_id = '12684'; 

if ($_GET['action'] === 'send') {
    $phone = $_POST['phone'] ?? '';
    
    if (empty($phone)) {
        echo json_encode(['status' => 'error', 'message' => 'Phone number is required.']);
        exit;
    }

    $phone = str_replace('+', '', $phone);
    if (substr($phone, 0, 1) === '0') {
        $phone = '63' . substr($phone, 1);
    }

    $otp = rand(100000, 999999);
    $_SESSION['temp_otp'] = $otp;
    $_SESSION['otp_phone'] = $phone;

    $message = "Your CampusWear code is: " . $otp;

    $url = "https://smsgateway24.com/getdata/smstosend";

    $postData = [
        'token' => $api_token,
        'email' => $email,
        'pass'  => $password,  
        'sendto' => $phone,
        'body' => $message,
        'device_id' => $device_id,
        'urgent' => 1
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['error']) && $result['error'] === 0) {
        echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully.']);
    } else {
        $error_msg = $result['message'] ?? 'Gateway rejected the request.';
        echo json_encode(['status' => 'error', 'message' => 'Gateway Error: ' . $error_msg]);
    }
    exit;
}

if ($_GET['action'] === 'verify') {
    $user_otp = $_POST['otp'] ?? '';
    
    if (isset($_SESSION['temp_otp']) && $user_otp == $_SESSION['temp_otp']) {
        $_SESSION['user_verified'] = true; 
        
        unset($_SESSION['temp_otp']); 
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid OTP.']);
    }
    exit;
}