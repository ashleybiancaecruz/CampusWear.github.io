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

if (empty($input) && $method === 'POST') {
    $input = $_POST;
}

$action = $_GET['action'] ?? $input['action'] ?? '';

if ($method === 'POST' && $action === 'delete') {
    handleDelete($conn, $input);
    exit;
}

switch ($method) {
    case 'GET':
        handleGet($conn, $_GET);
        break;
    case 'POST':
        handlePost($conn, $input);
        break;
    case 'PUT':
        handlePut($conn, $input);
        break;
    case 'DELETE':
        handleDelete($conn, $input);
        break;
    default:
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        break;
}

function handleGet($conn, $params) {
    $action = $params['action'] ?? 'list';
    
    if ($action === 'get' && isset($params['id'])) {
        $id = intval($params['id']);
        $stmt = $conn->prepare("SELECT m.*, o.name as org_name, o.type as org_type 
                                FROM merchandise m 
                                JOIN organizations o ON m.org_id = o.organizations_id 
                                WHERE m.merchandise_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'success', 'data' => $result->fetch_assoc()]);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Merchandise not found']);
        }
        $stmt->close();
        return;
    }
    
    $orgFilter = $params['org'] ?? 'all';
    $typeFilter = $params['type'] ?? 'all';
    
    $sql = "SELECT m.*, o.name as org_name, o.type as org_type 
            FROM merchandise m 
            JOIN organizations o ON m.org_id = o.organizations_id 
            WHERE 1=1";
    
    $params_array = [];
    $types = '';
    
    if ($orgFilter !== 'all') {
        $sql .= " AND o.name = ?";
        $params_array[] = $orgFilter;
        $types .= 's';
    }
    
    if ($typeFilter !== 'all') {
        $sql .= " AND o.type = ?";
        $params_array[] = $typeFilter;
        $types .= 's';
    }
    
    $sql .= " ORDER BY o.name, m.name";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params_array)) {
        $stmt->bind_param($types, ...$params_array);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $merchandise = [];
    while ($row = $result->fetch_assoc()) {
        $merchandise[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $merchandise]);
    $stmt->close();
}

function handlePost($conn, $input) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
        return;
    }
    
    $org_id = intval($input['org_id'] ?? 0);
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? '';
    $price = floatval($input['price'] ?? 0);
    $stock = intval($input['stock'] ?? 0);
    $size = $input['size'] ?? '';
    $color = $input['color'] ?? '';
    $image = 'default.jpg';
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/merchandise/';
        $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExt, $allowedExts)) {
            $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . '_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
                $image = $fileName;
            }
        }
    }
    
    if (empty($name) || $org_id <= 0 || $price <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }
    
    $stmt = $conn->prepare("INSERT INTO merchandise (org_id, name, description, price, stock, size, color, image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdisss", $org_id, $name, $description, $price, $stock, $size, $color, $image);
    
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Merchandise created successfully', 
            'id' => $conn->insert_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

function handlePut($conn, $input) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
        return;
    }
    
    $id = intval($input['id'] ?? 0);
    $org_id = intval($input['org_id'] ?? 0);
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? '';
    $price = floatval($input['price'] ?? 0);
    $stock = intval($input['stock'] ?? 0);
    $size = $input['size'] ?? '';
    $color = $input['color'] ?? '';
    $image = null;
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/merchandise/';
        $fileExt = strtolower(pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExt, $allowedExts)) {
            $fileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . '_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetPath)) {
                $image = $fileName;
            }
        }
    }
    
    if ($id <= 0 || empty($name) || $org_id <= 0 || $price <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        return;
    }
    
    $sql = "UPDATE merchandise SET 
            org_id = ?, name = ?, description = ?, price = ?, stock = ?, size = ?, color = ?";
    
    if ($image !== null) {
        $sql .= ", image = ?";
    }
    
    $sql .= " WHERE merchandise_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($image !== null) {
        $stmt->bind_param("issdisssi", $org_id, $name, $description, $price, $stock, $size, $color, $image, $id);
    } else {
        $stmt->bind_param("issdissi", $org_id, $name, $description, $price, $stock, $size, $color, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Merchandise updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

function handleDelete($conn, $input) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
        return;
    }
    
    $id = intval($input['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        return;
    }
    
    $checkStmt = $conn->prepare("SELECT cart_id FROM cart WHERE merchandise_id = ? LIMIT 1");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $hasCartItems = $checkStmt->get_result()->num_rows > 0;
    $checkStmt->close();
    
    if ($hasCartItems) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Cannot delete merchandise that is in user carts. Please remove from carts first.']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM merchandise WHERE merchandise_id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Merchandise deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}
?>
