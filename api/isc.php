<?php
require_once __DIR__ . '/../config/config.php';
@include_once __DIR__ . '/../config/database_isc.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'is_member' => false, 'message' => 'Please login first.']);
    exit;
}

$email = $_SESSION['email'] ?? '';
$action = $_GET['action'] ?? 'check_member';

if ($action === 'check_member') {
    $stmt = $conn->prepare("SELECT status FROM isc_members WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        echo json_encode(['status' => 'success', 'is_member' => true, 'discount_percent' => 10]);
        exit;
    }

    $iscApiUrl = getenv('ISC_API_URL') ?: '';
    
    if (!empty($iscApiUrl)) {
        $ch = curl_init($iscApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => $email]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $iscResponse = json_decode($response, true);
            if (isset($iscResponse['status']) && $iscResponse['status'] === 'member') {
                $name = $conn->real_escape_string($iscResponse['name'] ?? '');
                $email_clean = $conn->real_escape_string($email);
                $mbID = intval($iscResponse['member_id'] ?? 0);
                
                $conn->query("INSERT INTO isc_members (isc_member_id, email, name, status, isc_synced_at) 
                              VALUES ($mbID, '$email_clean', '$name', 'active', NOW())
                              ON DUPLICATE KEY UPDATE status='active', isc_synced_at=NOW()");
                
                echo json_encode(['status' => 'success', 'is_member' => true, 'discount_percent' => 10]);
                exit;
            }
        }
    }

    if (isset($isc_conn) && $isc_conn !== null) {
        $isc_stmt = $isc_conn->prepare("SELECT mbID, mbEmail, mbFname, mbLname FROM tbl_members WHERE mbEmail = ? LIMIT 1");
        $isc_stmt->bind_param("s", $email);
        $isc_stmt->execute();
        $isc_res = $isc_stmt->get_result();

        if ($isc_res->num_rows > 0) {
            $row = $isc_res->fetch_assoc();
            $name = $conn->real_escape_string($row['mbFname'] . ' ' . $row['mbLname']);
            $email_clean = $conn->real_escape_string($email);
            $mbID = intval($row['mbID']);
            
            $conn->query("INSERT INTO isc_members (isc_member_id, email, name, status, isc_synced_at) 
                          VALUES ($mbID, '$email_clean', '$name', 'active', NOW())
                          ON DUPLICATE KEY UPDATE status='active', isc_synced_at=NOW()");
            
            echo json_encode(['status' => 'success', 'is_member' => true, 'discount_percent' => 10]);
            exit;
        }
    }

    echo json_encode(['status' => 'success', 'is_member' => false, 'discount_percent' => 0]);
    exit;
}

