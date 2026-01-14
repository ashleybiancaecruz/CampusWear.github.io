<?php

ob_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$config_file = __DIR__ . '/../../config/config.php';

if (file_exists($config_file)) {
    require_once $config_file;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Config file not found.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'status') {
    $email = $_GET['email'] ?? '';
    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Email is required']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT feedback_id, status, created_at, updated_at FROM feedback WHERE email = ? ORDER BY created_at DESC LIMIT 10");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = $row;
    }
    $stmt->close();
    echo json_encode(['status' => 'success', 'data' => $feedback]);
    exit;
}

if ($method === 'POST') {
    $name     = isset($_POST['name']) ? $_POST['name'] : 'Anonymous';
    $email    = isset($_POST['email']) ? $_POST['email'] : '';
    $mobile   = isset($_POST['mobile_number']) ? $_POST['mobile_number'] : '';
    $content  = isset($_POST['body']) ? $_POST['body'] : '';
    $web_name = isset($_POST['website_name']) ? $_POST['website_name'] : 'Campus Wear';
    $category = isset($_POST['category_name']) ? $_POST['category_name'] : 'feedback';

    if (empty($email) || empty($content)) {
        echo json_encode(['status' => 'error', 'message' => 'Email and Body are required.']);
        exit;
    }

    $sql = "INSERT INTO feedback (name, email, mobile_number, body, website_name, category_name) 
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssss", $name, $email, $mobile, $content, $web_name, $category);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Feedback submitted!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} 
elseif ($method === 'PUT') {
    $putData = json_decode(file_get_contents("php://input"), true);

    $fields = [];
    $params = [];

    if (isset($putData['status'])) {
        $fields[] = "status = ?";
        $params[] = $putData['status'];
    }

    if (empty($fields) || !isset($putData['feedback_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing feedback_id or fields']);
        return;
    }

    $sql = "UPDATE feedback SET " . implode(", ", $fields) . " WHERE feedback_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $params[] = $putData['feedback_id'];
        $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Feedback updated successfully']);
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}


ob_end_flush();
exit;