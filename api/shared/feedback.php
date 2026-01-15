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
} elseif ($method === 'PUT') {
    $putData = json_decode(file_get_contents("php://input"), true);

    $feedback_id = isset($putData['feedback_id']) ? $putData['feedback_id'] : null;
    $new_status  = isset($putData['status']) ? $putData['status'] : null;

    // Validate required identifiers
    if (!$feedback_id || !$new_status) {
        echo json_encode(['status' => 'error', 'message' => 'feedback_id and status are required for update.']);
        exit;
    }

    // Build dynamic SQL
    $fields = [];
    $params = [];

    if ($new_status) {
        $fields[] = "status = ?";
        $params[] = $new_status;
    }

    if (empty($fields)) {
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        exit;
    }

    $sql = "UPDATE feedback SET " . implode(", ", $fields) . " WHERE feedback_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $params[] = $feedback_id;
        $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Feedback updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    $result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");

    if ($result) {
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($data, JSON_PRETTY_PRINT);
    } else {
        echo json_encode([]);
    }
}

ob_end_flush();
exit;