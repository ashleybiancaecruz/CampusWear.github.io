<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$config_file = __DIR__ . '/../../config/config.php';
if (!file_exists($config_file)) {
    echo json_encode(['success' => false, 'message' => 'Config file not found.']);
    exit;
}
require_once $config_file;

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $sql = "SELECT feedback_id, name, email, mobile_number, body, website_name, category_name, status 
            FROM feedback ORDER BY feedback_id DESC";
    $result = $conn->query($sql);

    if ($result) {
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $rows]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

elseif ($method === 'POST') {
    $name     = $_POST['name'] ?? 'Anonymous';
    $email    = $_POST['email'] ?? '';
    $mobile   = $_POST['mobile_number'] ?? '';
    $content  = $_POST['body'] ?? '';
    $web_name = $_POST['website_name'] ?? 'Campus Wear';
    $category = $_POST['category_name'] ?? 'feedback';

    if (empty($email) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Email and Body are required.']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit;
    }

    $sql = "INSERT INTO feedback (name, email, mobile_number, body, website_name, category_name) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssssss", $name, $email, $mobile, $content, $web_name, $category);
        $response = $stmt->execute()
            ? ['success' => true, 'message' => 'Feedback submitted!']
            : ['success' => false, 'message' => $stmt->error];
        echo json_encode($response);
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

elseif ($method === 'PUT') {
    $putData = json_decode(file_get_contents("php://input"), true);
    if (!isset($putData['feedback_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing feedback_id']);
        exit;
    }

    $fields = [];
    $params = [];
    $types  = '';

    if (isset($putData['status'])) {
        $fields[] = "status = ?";
        $params[] = $putData['status'];
        $types   .= 's';
    }

    if (empty($fields)) {
        echo json_encode(['success' => false, 'message' => 'No fields to update']);
        exit;
    }

    $params[] = $putData['feedback_id'];
    $types   .= 'i';

    $sql = "UPDATE feedback SET " . implode(", ", $fields) . " WHERE feedback_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Feedback updated successfully']);
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }
}

else {
    echo json_encode(['success' => false, 'message' => 'Unsupported request method']);
}