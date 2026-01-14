<?php
date_default_timezone_set('Asia/Manila');

function generateOTP()
{
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function storeOTP($phone, $otp, $conn, $userId = 0)
{
    $dbPhone = ltrim($phone, '+');
    $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $conn->prepare("INSERT INTO otp_log (user_id, phone, code, expires_at, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->bind_param("isss", $userId, $dbPhone, $otp, $expires);
    return $stmt->execute();
}

function verifyOTP($phone, $otp, $conn)
{
    $dbPhone = ltrim($phone, '+');
    $now = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("SELECT id FROM otp_log WHERE phone = ? AND code = ? AND status = 'pending' AND expires_at > ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("sss", $dbPhone, $otp, $now);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $conn->query("UPDATE otp_log SET status = 'verified' WHERE phone = '$dbPhone' AND code = '$otp'");
        $stmtVerify = $conn->prepare("INSERT INTO otp_verifications (phone, otp, verified, expires_at) VALUES (?, ?, 1, ?) ON DUPLICATE KEY UPDATE verified=1");
        $stmtVerify->bind_param("sss", $dbPhone, $otp, $now);
        $stmtVerify->execute();
        return true;
    }
    return false;
}

function sendOTPviaSMS($phone, $otp)
{
    $token = '2ad954c7e94d4e0122edc7b460444ea8';
    $device_id = '12684';
    $message = "Your Campus Wear Purchase Verification code is: " . $otp . ". Reply YES to confirm pick-up.";

    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($cleanPhone) < 10) {
        error_log("Invalid phone number format: " . $phone);
        return false;
    }

    $data = array(
        'token' => $token,
        'sendto' => $cleanPhone,
        'body' => $message,
        'device_id' => $device_id,
        'sim' => 1
    );

    $ch = curl_init('https://smsgateway24.com/get_api/send_sms');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $res = json_decode($response, true); 

    $gatewayOk = false;
    if ($httpCode === 200) {
        if (is_array($res)) {
            if (isset($res['error']) && (int)$res['error'] === 0) $gatewayOk = true;
            if (isset($res['status']) && strtolower((string)$res['status']) === 'success') $gatewayOk = true;
            if (isset($res['success']) && ($res['success'] === true || $res['success'] === 1 || $res['success'] === '1')) $gatewayOk = true;
        } else if (is_string($response) && $response !== '') {
            $txt = strtolower($response);
            if (strpos($txt, 'success') !== false || trim($txt) === 'ok') $gatewayOk = true;
        }
    }

    if (!$gatewayOk) {
        error_log("GATEWAY ERROR: " . $response);
        return false;
    }
    return true;
}