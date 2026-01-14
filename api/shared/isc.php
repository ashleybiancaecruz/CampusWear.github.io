<?php

ob_start(); 

$config_file = __DIR__ . '/../../config/config.php';
if (file_exists($config_file)) {
    require_once $config_file; 
} else {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Config not found.']));
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


if (!isset($isc_conn)) {
    $isc_conn = $conn; 
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Login is required."]);
        exit;
    }

    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);
    
    $first_name  = $data['first_name'] ?? ($_POST['first_name'] ?? '');
    $last_name   = $data['last_name'] ?? ($_POST['last_name'] ?? '');
    $middle_name = $data['middle_name'] ?? ($_POST['middle_name'] ?? '');
    $suffix      = $data['suffix'] ?? ($_POST['suffix'] ?? '');
    $salutation  = $data['salutation'] ?? ($_POST['salutation'] ?? '');
    $pronoun     = $data['pronoun'] ?? ($_POST['pronoun'] ?? '');
    $birth_date  = $data['birth_date'] ?? ($_POST['birth_date'] ?? '');
    $dept        = $data['department'] ?? ($_POST['department'] ?? '');
    $section     = $data['section'] ?? ($_POST['section'] ?? '');
    $institution = $data['institution'] ?? ($_POST['institution'] ?? '');
    $email       = $data['email'] ?? ($_POST['email'] ?? '');
    $phone       = $data['phone'] ?? ($_POST['phone'] ?? '');
    $source      = $data['source'] ?? ($_POST['source'] ?? 'Web');

    try {
        $sql = "INSERT INTO isc_applications 
                (user_id, first_name, last_name, middle_name, suffix, salutation, pronoun, birth_date, department, section, institution, email, phone, source, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $isc_conn->prepare($sql);
        $user_id = $_SESSION['user_id'];
        
        $stmt->bind_param("isssssssssssss", 
            $user_id, $first_name, $last_name, $middle_name, 
            $suffix, $salutation, $pronoun, $birth_date, 
            $dept, $section, $institution, $email, $phone, $source
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Application Saved!"]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} 

else {
    try {
        $sql = "SELECT * FROM isc_applications ORDER BY isc_applications_id DESC";
        $result = $isc_conn->query($sql);
        $apps = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) { $apps[] = $row; }
        }
        echo json_encode($apps, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

ob_end_flush();
exit;