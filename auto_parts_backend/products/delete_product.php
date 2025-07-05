<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include_once("../config/db.php");

// ✅ هنا بنضيف التحقق من نوع الطلب (GET vs POST)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "This endpoint only accepts POST requests with JSON payload.";
    exit;
}

// 🟡 استلام البيانات
$rawInput = file_get_contents("php://input");

$data = json_decode($rawInput);

// تحقق إن البيانات Object
if (!is_object($data)) {
    echo json_encode(["status" => "error", "message" => "Invalid request format"]);
    exit;
}

// التحقق من ID
if (!property_exists($data, 'id')) {
  echo json_encode(["status" => "error", "message" => "Product ID is required"]);
    exit;
}

$id = intval($data->id);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Product ID is required"]);
    exit;
}

// تنفيذ الحذف
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Product deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete product"]);
}

$stmt->close();
$conn->close();
