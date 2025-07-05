<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("../config/db.php");

// الحصول على user_id
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User ID is required"]);
    exit;
}

$user_id = $data['user_id'];

// 1. جلب كل الطلبات لهذا المستخدم
$order_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];

while ($order = $result->fetch_assoc()) {
    $order_id = $order['id'];

    // 2. جلب العناصر المرتبطة بهذا الطلب
    $item_sql = "
        SELECT 
            oi.product_id, 
            p.name AS product_name, 
            oi.price, 
            oi.quantity
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        WHERE oi.order_id = ?
    ";

    $item_stmt = $conn->prepare($item_sql);
    $item_stmt->bind_param("i", $order_id);
    $item_stmt->execute();
    $item_result = $item_stmt->get_result();

    $items = [];
    while ($item = $item_result->fetch_assoc()) {
        $items[] = $item;
    }

    $order['items'] = $items;
    $orders[] = $order;

    $item_stmt->close();
}

$stmt->close();
$conn->close();

echo json_encode([
    "status" => "success",
    "orders" => $orders
]);
