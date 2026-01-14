<?php
ob_clean(); 
error_reporting(0); 
ini_set('display_errors', 0);
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL & ~E_NOTICE); 
ini_set('display_errors', 0); 

require_once __DIR__ . '/../config/config.php';
if (file_exists(__DIR__ . '/../config/database_isc.php')) {
    require_once __DIR__ . '/../config/database_isc.php';
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if (!isset($conn) || $conn->connect_error) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Main database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = intval($_SESSION['user_id']);
$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (empty($input) && in_array($method, ['POST', 'PUT', 'DELETE'])) {
    $input = $_POST;
}

switch ($method) {
    case 'GET':
        handleGet($conn, $user_id);
        break;
    case 'POST':
        handlePost($conn, $user_id, $input);
        break;
    case 'PUT':
    case 'DELETE':
        if ($method === 'PUT') handlePut($conn, $user_id, $input);
        else handleDelete($conn, $user_id, $input);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}

function handleGet($conn, $user_id) {
    $user_email = $_SESSION['email'] ?? '';
    $is_active_member = false;
    
    if (!empty($user_email) && function_exists('getISCMemberByEmail')) {
        global $isc_conn; 
        $isc_member = getISCMemberByEmail($user_email, $conn, $isc_conn ?? null);
        $is_active_member = ($isc_member && isset($isc_member['status']) && $isc_member['status'] === 'active');
    }

    $query = "SELECT c.*, m.name, m.price, m.stock, m.image, o.name as org_name 
              FROM cart c
              JOIN merchandise m ON c.merchandise_id = m.merchandise_id
              JOIN organizations o ON m.org_id = o.organizations_id
              WHERE c.user_id = ?
              ORDER BY c.created_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $price = floatval($row['price']);
        $row['is_isc_member'] = $is_active_member;
        $row['final_price'] = $is_active_member ? ($price * 0.90) : $price;
        $items[] = $row;
    }
    
    echo json_encode([
        'status' => 'success', 
        'data' => $items,
        'is_isc_member' => $is_active_member
    ]);
    $stmt->close();
}

function handlePost($conn, $user_id, $input) {
    $action = $input['action'] ?? $_GET['action'] ?? 'add';
    
    if ($action === 'clear') {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $result = $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Cart cleared' : 'Failed to clear cart']);
        return;
    }
    
    if ($action === 'delete') {
        $cart_id = intval($input['cart_id'] ?? 0);
        if ($cart_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid cart ID']);
            return;
        }
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Item removed' : 'Failed to remove item']);
        return;
    }

    if ($action === 'update') {
        $cart_id = intval($input['cart_id'] ?? 0);
        $qty = intval($input['quantity'] ?? 1);
        
        if ($cart_id <= 0 || $qty <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
            return;
        }
        
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("iii", $qty, $cart_id, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        echo json_encode(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Updated' : 'Failed to update']);
        return;
    }

    if ($action === 'add' || $action === '') {
        $m_id = intval($input['merchandise_id'] ?? $input['merch_id'] ?? 0);
        $qty = intval($input['quantity'] ?? 1);

        if ($m_id <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid merchandise ID']);
            return;
        }

        $checkStmt = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND merchandise_id = ?");
        $checkStmt->bind_param("ii", $user_id, $m_id);
        $checkStmt->execute();
        $res = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        $cartId = null;

        if ($res) {
            $new_qty = $res['quantity'] + $qty;
            $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
            $updateStmt->bind_param("ii", $new_qty, $res['cart_id']);
            $result = $updateStmt->execute();
            $updateStmt->close();
            $cartId = (int)$res['cart_id'];
        } else {
            $insertStmt = $conn->prepare("INSERT INTO cart (user_id, merchandise_id, quantity) VALUES (?, ?, ?)");
            $insertStmt->bind_param("iii", $user_id, $m_id, $qty);
            $result = $insertStmt->execute();
            $cartId = (int)$insertStmt->insert_id;
            $insertStmt->close();
        }
        
        echo json_encode([
            'status' => $result ? 'success' : 'error',
            'message' => $result ? 'Cart updated' : 'Database error',
            'cart_id' => $cartId
        ]);
        return;
    }
    
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}

function handlePut($conn, $user_id, $input) { handlePost($conn, $user_id, array_merge($input, ['action' => 'update'])); }
function handleDelete($conn, $user_id, $input) { handlePost($conn, $user_id, array_merge($input, ['action' => 'delete'])); }