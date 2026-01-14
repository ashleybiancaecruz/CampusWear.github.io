<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Login is required to apply."]);
        exit;
    }

    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
        exit;
    }

    try {
        $sql = "INSERT INTO isc_applications 
                (user_id, first_name, last_name, middle_name, suffix, salutation, pronoun, birth_date, department, section, institution, email, phone, source, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $stmt = $conn->prepare($sql);
        $user_id = $_SESSION['user_id'] ?? null;
        
        $stmt->bind_param("isssssssssssss", 
            $user_id, $data['first_name'], $data['last_name'], $data['middle_name'], 
            $data['suffix'], $data['salutation'], $data['pronoun'], $data['birth_date'], 
            $data['department'], $data['section'], $data['institution'], 
            $data['email'], $data['phone'], $data['source']
        );

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Application Saved!"]);
        } else {
            throw new Exception($stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}

try {
    $sql = "SELECT * FROM isc_applications ORDER BY isc_applications_id DESC";
    $result = $conn->query($sql);
    $apps = [];
    while ($row = $result->fetch_assoc()) { $apps[] = $row; }
    echo json_encode($apps, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>