<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['order_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing order ID"]);
    exit;
}

$order_id = $data['order_id'];

// التأكد أن الطلب حالته pending قبل الإلغاء
$check = $conn->prepare("SELECT status FROM orders WHERE id = ?");
$check->bind_param("i", $order_id);
$check->execute();
$result = $check->get_result();
$order = $result->fetch_assoc();
$check->close();

if (!$order || $order['status'] !== 'pending') {
    echo json_encode(["status" => "error", "message" => "Order cannot be cancelled"]);
    exit;
}

// التحديث إلى cancelled
$stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
$stmt->bind_param("i", $order_id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Order cancelled"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to cancel order"]);
}
$stmt->close();
$conn->close();
