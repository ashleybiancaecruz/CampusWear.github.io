<?php
require_once __DIR__ . '/../../config/config.php'; 
@include_once __DIR__ . '/../../config/database_isc.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'is_member' => false, 'message' => 'Please login first.']);
    exit;
}

$email = $_SESSION['email'] ?? '';
$action = $_GET['action'] ?? 'check';

if ($action === 'check') {
    $stmt = $conn->prepare("SELECT status FROM isc_members WHERE email = ? AND status = 'active' LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        echo json_encode(['status' => 'success', 'is_member' => true, 'discount_percent' => 10]);
        exit;
    }

    if (isset($isc_conn) && $isc_conn !== null) {
        $isc_stmt = $isc_conn->prepare("SELECT mbID, mbEmail, mbFname, mbLname FROM tbl_members WHERE mbEmail = ? LIMIT 1");
        $isc_stmt->bind_param("s", $email);
        $isc_stmt->execute();
        $isc_res = $isc_stmt->get_result();

        if ($isc_res->num_rows > 0) {
            $row = $isc_res->fetch_assoc();
            $name = $row['mbFname'] . ' ' . $row['mbLname'];
            $conn->query("INSERT INTO isc_members (isc_member_id, email, name, status, isc_synced_at) 
                          VALUES ({$row['mbID']}, '$email', '$name', 'active', NOW())
                          ON DUPLICATE KEY UPDATE status='active'");
            
            echo json_encode(['status' => 'success', 'is_member' => true, 'discount_percent' => 10]);
            exit;
        }
    }

    echo json_encode(['status' => 'success', 'is_member' => false, 'discount_percent' => 0]);
    exit;
}