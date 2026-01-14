<?php
require_once 'config/config.php';

header('Content-Type: text/plain');

$incoming_phone = '';
if (isset($_POST['phone'])) {
    $incoming_phone = preg_replace('/[^0-9]/', '', $_POST['phone']);
} elseif (isset($_POST['From'])) {
    $incoming_phone = preg_replace('/[^0-9]/', '', $_POST['From']);
} elseif (isset($_POST['from'])) {
    $incoming_phone = preg_replace('/[^0-9]/', '', $_POST['from']);
}

$incoming_msg = '';
if (isset($_POST['message'])) {
    $incoming_msg = strtoupper(trim($_POST['message']));
} elseif (isset($_POST['Body'])) {
    $incoming_msg = strtoupper(trim($_POST['Body']));
} elseif (isset($_POST['body'])) {
    $incoming_msg = strtoupper(trim($_POST['body']));
} elseif (isset($_POST['text'])) {
    $incoming_msg = strtoupper(trim($_POST['text']));
}

if (empty($incoming_phone) || empty($incoming_msg)) {
    echo 'OK';
    exit;
}

$response = '';

if ($incoming_msg === 'CANCEL' || $incoming_msg === 'CANCEL ORDER') {
    $phone_clean = $conn->real_escape_string($incoming_phone);
    $conn->query("UPDATE purchases SET payment_status = 'cancelled' WHERE phone = '$phone_clean' AND payment_status = 'pending' LIMIT 1");
    $response = 'CampusWear: Your order has been cancelled as requested. Thank you.';
} elseif ($incoming_msg === 'YES') {
    $phone_clean = $conn->real_escape_string($incoming_phone);
    $result = $conn->query("SELECT purchase_id, merch_name, total_amount FROM purchases WHERE phone = '$phone_clean' AND payment_status = 'completed' ORDER BY created_at DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $conn->query("UPDATE purchases SET status = 'pickup_confirmed' WHERE purchase_id = " . intval($order['purchase_id']));
        $response = 'CampusWear: Pick-up confirmed for ' . $order['merch_name'] . '. Your order is ready at COMSOC Office, New Building, 3rd Floor, Room 304.';
    } else {
        $response = 'CampusWear: We could not find a completed order for this number. Please contact support if you have questions.';
    }
} else {
    $response = 'CampusWear: Thank you for contacting us. Reply YES to confirm pick-up, or CANCEL to cancel your order.';
}

echo $response;
?>