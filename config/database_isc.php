<?php
$is_localhost = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');

if ($is_localhost) {
    $isc_host = "localhost:3308"; 
    $isc_user = "root";
    $isc_pass = "";
    $isc_db   = "campuswear_db"; 
} else {
    $isc_host = "sql111.infinityfree.com";
    $isc_user = "if0_40891695";
    $isc_pass = "Your_Password"; 
    $isc_db   = "if0_40891695_isc_applications";
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $isc_conn = new mysqli($isc_host, $isc_user, $isc_pass, $isc_db);
} catch (Exception $e) {
    $isc_conn = null;
}

function getISCMemberByEmail($email, $conn, $isc_conn = null) {
    $email = trim(strtolower($email ?? ''));
    if ($email === '') {
        return null;
    }

    try {
        $stmt = $conn->prepare("SELECT isc_member_id, email, name, status FROM isc_members WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $stmt->close();
            return $row;
        }
        $stmt->close();
    } catch (Exception $e) {
    }

    $sharedUrl = 'https://unsatirical-sharda-calorimetric.ngrok-free.dev/ISC-Student-Organization-System/api-connections/sharedMembers-api.php';
    $ch = curl_init($sharedUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && is_string($response) && $response !== '') {
        $data = json_decode($response, true);
        if (is_array($data)) {
            foreach ($data as $item) {
                $remoteEmail = strtolower(trim($item['mbEmail'] ?? ''));
                if ($remoteEmail !== '' && $remoteEmail === $email) {
                    $mbID = intval($item['mbID'] ?? 0);
                    $name = trim(($item['mbFname'] ?? '') . ' ' . ($item['mbLname'] ?? ''));

                    $email_clean = $conn->real_escape_string($email);
                    $name_clean = $conn->real_escape_string($name);
                    
                    $conn->query("INSERT INTO isc_members (isc_member_id, email, name, status, isc_synced_at) 
                                  VALUES ($mbID, '$email_clean', '$name_clean', 'active', NOW())
                                  ON DUPLICATE KEY UPDATE status='active', name=VALUES(name), isc_synced_at=NOW()");

                    return ['isc_member_id' => $mbID, 'email' => $email, 'name' => $name, 'status' => 'active'];
                }
            }
        }
    }

    if ($isc_conn) {
        try {
            $stmt2 = $isc_conn->prepare("SELECT mbID, mbEmail, mbFname, mbLname FROM tbl_members WHERE LOWER(mbEmail) = ? LIMIT 1");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $res2 = $stmt2->get_result();
            
            if ($res2 && $res2->num_rows > 0) {
                $row = $res2->fetch_assoc();
                $mbID = intval($row['mbID']);
                $name = trim(($row['mbFname'] ?? '') . ' ' . ($row['mbLname'] ?? ''));

                $email_clean = $conn->real_escape_string($email);
                $name_clean = $conn->real_escape_string($name);
                
                $conn->query("INSERT INTO isc_members (isc_member_id, email, name, status, isc_synced_at) 
                              VALUES ($mbID, '$email_clean', '$name_clean', 'active', NOW())
                              ON DUPLICATE KEY UPDATE status='active', name=VALUES(name), isc_synced_at=NOW()");

                $stmt2->close();
                return ['isc_member_id' => $mbID, 'email' => $email, 'name' => $name, 'status' => 'active'];
            }
            $stmt2->close();
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }

    return null;
}
?>