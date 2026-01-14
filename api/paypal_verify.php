<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json');

function addBusinessDays($startDate, $businessDays) {
    $date = new DateTime($startDate);
    $addedDays = 0;
    while ($addedDays < $businessDays) {
        $date->modify('+1 day');
        if ($date->format('w') != 0 && $date->format('w') != 6) { $addedDays++; }
    }
    return $date->format('Y-m-d');
}

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

if ($action === 'verify') {
    $order_id = $_POST['orderID'] ?? '';
    $merch_id = intval($_POST['merch_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $total_amount = floatval($_POST['total_amount'] ?? 0);

    $merch = $conn->query("SELECT m.*, o.name as org_name FROM merchandise m JOIN organizations o ON m.org_id = o.organizations_id WHERE m.merchandise_id = $merch_id")->fetch_assoc();

    if (!$merch) {
        echo json_encode(['status' => 'error', 'message' => 'Merchandise not found']);
        exit;
    }

    $username = $_SESSION['user'];
    $price = floatval($merch['price']);
    $transaction_id = 'PAYPAL_' . time();
    $pickup_date = addBusinessDays(date('Y-m-d'), 3);

    $sql = "INSERT INTO purchases (username, merch_id, merch_name, quantity, price, total_amount, payment_status, paypal_order_id, paypal_transaction_id, payment_method, pickup_date) 
            VALUES ('$username', $merch_id, '" . $conn->real_escape_string($merch['name']) . "', 
            $quantity, $price, $total_amount, 'completed', '$order_id', '$transaction_id', 'paypal', '$pickup_date')";

    if ($conn->query($sql)) {
        $conn->query("UPDATE merchandise SET stock = stock - $quantity WHERE merchandise_id = $merch_id");
        echo json_encode(['status' => 'success', 'message' => 'Purchase completed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
    }
    exit;
}
 elseif ($action === 'verify_cart') {
    $order_id = $_POST['orderID'] ?? '';
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $discount = floatval($_POST['discount'] ?? 0);

    if (empty($order_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['user'];
    $user_email = $_SESSION['email'] ?? '';

    $sql = "SELECT c.*, m.name, m.price, m.stock, o.name as org_name
            FROM cart c
            JOIN merchandise m ON c.merchandise_id = m.merchandise_id
            JOIN organizations o ON m.org_id = o.organizations_id
            WHERE c.user_id = $user_id";

    $result = $conn->query($sql);
    $cartItems = [];
    $calculated_subtotal = 0;

    while ($row = $result->fetch_assoc()) {
        $cartItems[] = $row;
        $calculated_subtotal += $row['price'] * $row['quantity'];
    }

    if (empty($cartItems)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }

    $transaction_id = 'PAYPAL_' . time() . '_' . uniqid();
    $pickup_date = addBusinessDays(date('Y-m-d'), 7);
    $success_count = 0;

    foreach ($cartItems as $item) {
        $merch_id = $item['merchandise_id'];
        $merch_name = $conn->real_escape_string($item['name']);
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $item_total = $price * $quantity;
        $item_ratio = $calculated_subtotal > 0 ? ($item_total / $calculated_subtotal) : 0;
        $final_item_amount = $item_total - ($discount * $item_ratio);

        $sql = "INSERT INTO purchases (username, merch_id, merch_name, quantity, price, total_amount, payment_status, paypal_order_id, paypal_transaction_id, payment_method, pickup_date)
                VALUES ('$username', $merch_id, '$merch_name', $quantity, $price, $final_item_amount, 'completed', '$order_id', '$transaction_id', 'paypal', '$pickup_date')";

        if ($conn->query($sql)) {
            $conn->query("UPDATE merchandise SET stock = stock - $quantity WHERE merchandise_id = $merch_id");
            $success_count++;
        }
    }

    if ($success_count == count($cartItems)) {
        $conn->query("DELETE FROM cart WHERE user_id = $user_id");
        echo json_encode(['status' => 'success', 'message' => 'Cart purchase completed', 'transaction_id' => $transaction_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Partial error during purchase']);
    }
    exit;

} elseif ($action === 'status') {
    $order_id = $_GET['order_id'] ?? '';
    if (empty($order_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Order ID required']);
        exit;
    }
    $result = $conn->query("SELECT * FROM purchases WHERE paypal_order_id = '$order_id' AND username = '" . $_SESSION['user'] . "'");
    if ($result->num_rows > 0) {
        echo json_encode(['status' => 'success', 'data' => $result->fetch_assoc()]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);