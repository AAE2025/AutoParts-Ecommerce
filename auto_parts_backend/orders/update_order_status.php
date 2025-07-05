<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
include_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->order_id) || !isset($data->status)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$order_id = $data->order_id;
$status = $data->status;

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Order status updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update order"]);
}

$stmt->close();
$conn->close();
