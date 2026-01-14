<?php

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'sync_members') {
    if ($isc_conn === null) {
        echo json_encode(['status' => 'error', 'message' => 'ISC database not available. Please ensure the database exists and connection is configured.']);
        exit;
    }
    
    $synced = 0;
    $errors = 0;
    
    $isc_result = $isc_conn->query("SELECT * FROM tbl_members");
    
    if ($isc_result) {
        while ($isc_member = $isc_result->fetch_assoc()) {
            $email = $isc_member['mbEmail'];
            $name = trim($isc_member['mbFname'] . ' ' . $isc_member['mbLname']);
            $mbID = intval($isc_member['mbID']);
            
            $sync_sql = "INSERT INTO isc_members (isc_member_id, email, name, status, isc_synced_at) 
                        VALUES ($mbID, 
                                '" . $conn->real_escape_string($email) . "', 
                                '" . $conn->real_escape_string($name) . "', 
                                'active', NOW())
                        ON DUPLICATE KEY UPDATE 
                            isc_member_id = $mbID,
                            name = '" . $conn->real_escape_string($name) . "',
                            status = 'active',
                            isc_synced_at = NOW()";
            
            if ($conn->query($sync_sql)) {
                $synced++;
            } else {
                $errors++;
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => "Synced $synced members. Errors: $errors"
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch ISC members']);
    }
    exit;
}

if ($action === 'sync_applications') {
    if ($isc_conn === null) {
        echo json_encode(['status' => 'error', 'message' => 'ISC database not available. Please ensure the database exists and connection is configured.']);
        exit;
    }
    
    $synced = 0;
    $errors = 0;
    $skipped = 0;
    
    $isc_result = $isc_conn->query("SELECT a.*, s.apStatusDesc 
                                    FROM tbl_applications a 
                                    LEFT JOIN tbl_applicationstatus s ON a.apStatusID = s.apStatusID");
    
    if ($isc_result) {
        while ($isc_app = $isc_result->fetch_assoc()) {
            $email = $isc_app['apEmail'];
            
            $user_result = $conn->query("SELECT id, username FROM users WHERE email = '" . $conn->real_escape_string($email) . "'");
            
            if ($user_result && $user_result->num_rows > 0) {
                $user = $user_result->fetch_assoc();
                
                $status = 'pending';
                if (isset($isc_app['apStatusDesc'])) {
                    $statusDesc = strtolower($isc_app['apStatusDesc']);
                    if ($statusDesc === 'approved') $status = 'approved';
                    elseif ($statusDesc === 'rejected') $status = 'rejected';
                    else $status = 'pending';
                }
                
                $full_name = trim($isc_app['apFname'] . ' ' . $isc_app['apLname']);
                $apID = intval($isc_app['apID']);
                
                $sync_sql = "INSERT INTO isc_applications (
                                user_id, username, email, full_name, isc_application_id, status, isc_synced_at
                            ) VALUES (
                                " . intval($user['id']) . ",
                                '" . $conn->real_escape_string($user['username']) . "',
                                '" . $conn->real_escape_string($email) . "',
                                '" . $conn->real_escape_string($full_name) . "',
                                $apID,
                                '$status',
                                NOW()
                            )
                            ON DUPLICATE KEY UPDATE
                                status = '$status',
                                isc_application_id = $apID,
                                isc_synced_at = NOW()";
                
                if ($conn->query($sync_sql)) {
                    $synced++;
                } else {
                    $errors++;
                }
            } else {
                $skipped++; 
            }
        }
        
        echo json_encode([
            'status' => 'success',
            'message' => "Synced $synced applications. Skipped: $skipped (no user found). Errors: $errors"
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch ISC applications']);
    }
    exit;
}

if ($action === 'sync_status') {
    $member_sync_count = $conn->query("SELECT COUNT(*) as count FROM isc_member_sync WHERE sync_status = 'synced'")->fetch_assoc()['count'] ?? 0;
    $app_sync_count = $conn->query("SELECT COUNT(*) as count FROM isc_application_sync WHERE sync_status = 'synced'")->fetch_assoc()['count'] ?? 0;
    $last_member_sync = $conn->query("SELECT MAX(last_synced_at) as last_sync FROM isc_member_sync")->fetch_assoc()['last_sync'] ?? null;
    $last_app_sync = $conn->query("SELECT MAX(last_synced_at) as last_sync FROM isc_application_sync")->fetch_assoc()['last_sync'] ?? null;
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'members_synced' => $member_sync_count,
            'applications_synced' => $app_sync_count,
            'last_member_sync' => $last_member_sync,
            'last_app_sync' => $last_app_sync,
            'isc_db_connected' => ($isc_conn !== null)
        ]
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
