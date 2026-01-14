<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'list') {
    $orgFilter = $_GET['org'] ?? 'all';
    $typeFilter = $_GET['type'] ?? 'all';
    
    $sql = "SELECT m.*, o.name as org_name, o.type as org_type 
            FROM merchandise m 
            JOIN organizations o ON m.org_id = o.organizations_id 
            WHERE 1=1";
    
    if ($orgFilter !== 'all') {
        $orgFilter = $conn->real_escape_string($orgFilter);
        $sql .= " AND o.name = '$orgFilter'";
    }
    
    if ($typeFilter !== 'all') {
        $typeFilter = $conn->real_escape_string($typeFilter);
        $sql .= " AND o.type = '$typeFilter'";
    }
    
    $sql .= " ORDER BY o.name, m.name";
    
    $result = $conn->query($sql);
    $merchandise = [];
    
    while ($row = $result->fetch_assoc()) {
        $merchandise[] = $row;
    }
    
    echo json_encode(['status' => 'success', 'data' => $merchandise]);
    exit;
}

if ($action === 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT m.*, o.name as org_name, o.type as org_type 
            FROM merchandise m 
            JOIN organizations o ON m.org_id = o.organizations_id 
            WHERE m.merchandise_id = $id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'success', 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Merchandise not found']);
    }
    exit;
}

if ($action === 'create') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
        exit;
    }
    
    $org_id = intval($_POST['org_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $size = $conn->real_escape_string($_POST['size'] ?? '');
    $color = $conn->real_escape_string($_POST['color'] ?? '');
    
    $sql = "INSERT INTO merchandise (org_id, name, description, price, stock, size, color) 
            VALUES ($org_id, '$name', '$description', $price, $stock, '$size', '$color')";
    
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Merchandise added successfully', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

if ($action === 'update') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
        exit;
    }
    
    $id = intval($_POST['id']);
    $org_id = intval($_POST['org_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $size = $conn->real_escape_string($_POST['size'] ?? '');
    $color = $conn->real_escape_string($_POST['color'] ?? '');
    
    $sql = "UPDATE merchandise SET 
            org_id = $org_id,
            name = '$name',
            description = '$description',
            price = $price,
            stock = $stock,
            size = '$size',
            color = '$color'
            WHERE merchandise_id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Merchandise updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

if ($action === 'delete') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Admin access required']);
        exit;
    }
    
    $id = intval($_POST['id']);
    $sql = "DELETE FROM merchandise WHERE merchandise_id = $id";
    
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Merchandise deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
?>
