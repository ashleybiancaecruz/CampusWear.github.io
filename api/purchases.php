<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (empty($input) && in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $input = $_POST;
}

$username = $_SESSION['user'];

switch ($method) {
    case 'GET':
        handleGet($conn, $username, $_GET);
        break;
    case 'POST':
        handlePost($conn, $username, $input);
        break;
    case 'PUT':
        handlePut($conn, $username, $input);
        break;
    case 'DELETE':
        handleDelete($conn, $username, $input);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}

function handleGet($conn, $username, $params) {
    $action = $params['action'] ?? 'list';
    
    if ($action === 'get' && isset($params['id'])) {
        $id = intval($params['id']);
        $stmt = $conn->prepare("SELECT * FROM purchases WHERE purchase_id = ? AND username = ?");
        $stmt->bind_param("is", $id, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'success', 'data' => $result->fetch_assoc()]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Purchase not found']);
        }
        $stmt->close();
        return;
    }
    
    $stmt = $conn->prepare("SELECT * FROM purchases WHERE username = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $purchases = [];
    while ($row = $result->fetch_assoc()) {
        $purchases[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $purchases]);
    $stmt->close();
}

function handlePost($conn, $username, $input) {
    $merchandise_id = intval($input['merchandise_id'] ?? 0);
    $quantity = intval($input['quantity'] ?? 1);
    $shipping_address = $input['shipping_address'] ?? '';
    $total_amount = floatval($input['total_amount'] ?? 0);
    $payment_status = $input['payment_status'] ?? 'pending';
    $payment_method = $input['payment_method'] ?? 'paypal';
    $transaction_id = $input['transaction_id'] ?? '';
    
    if ($merchandise_id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid merchandise ID']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT m.*, o.name as org_name 
                           FROM merchandise m 
                           JOIN organizations o ON m.org_id = o.organizations_id 
                           WHERE m.merchandise_id = ?");
    $stmt->bind_param("i", $merchandise_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $merch = $result->fetch_assoc();
    $stmt->close();
    
    if (!$merch) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Merchandise not found']);
        return;
    }
    
    $price = floatval($merch['price']);
    $calculated_total = $price * $quantity;
    
    if ($total_amount <= 0) {
        $total_amount = $calculated_total;
    }
    
    $merch_name = $conn->real_escape_string($merch['name']);
    $org_name = $conn->real_escape_string($merch['org_name']);
    $shipping_address = $conn->real_escape_string($shipping_address);
    $payment_method = $conn->real_escape_string($payment_method);
    $transaction_id = $conn->real_escape_string($transaction_id);
    
    $stmt = $conn->prepare("INSERT INTO purchases (username, merch_id, merch_name, org_name, quantity, price, total_amount, shipping_address, payment_status, payment_method, paypal_transaction_id) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissidssss", $username, $merchandise_id, $merch_name, $org_name, $quantity, $price, $total_amount, $shipping_address, $payment_status, $payment_method, $transaction_id);
    
    if ($stmt->execute()) {
        $conn->query("UPDATE merchandise SET stock = stock - $quantity WHERE merchandise_id = $merchandise_id");
        echo json_encode([
            'status' => 'success', 
            'message' => 'Purchase created successfully',
            'id' => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

function handlePut($conn, $username, $input) {
    $id = intval($input['id'] ?? 0);
    $payment_status = $input['payment_status'] ?? null;
    $shipping_address = $input['shipping_address'] ?? null;
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }
    
    $updates = [];
    $params = [];
    $types = '';
    
    if ($payment_status !== null) {
        $updates[] = "payment_status = ?";
        $params[] = $payment_status;
        $types .= 's';
    }
    
    if ($shipping_address !== null) {
        $updates[] = "shipping_address = ?";
        $params[] = $shipping_address;
        $types .= 's';
    }
    
    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No fields to update']);
        return;
    }
    
    $sql = "UPDATE purchases SET " . implode(', ', $updates) . " WHERE purchase_id = ? AND username = ?";
    $params[] = $id;
    $params[] = $username;
    $types .= 'is';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Purchase updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

function handleDelete($conn, $username, $input) {
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM purchases WHERE purchase_id = ? AND username = ?");
    $stmt->bind_param("is", $id, $username);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Purchase deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}
?>
