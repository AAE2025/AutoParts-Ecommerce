<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');
require_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

// ✅ تحقق من البيانات المطلوبة
if (
    !isset($data['user_id']) || 
    !isset($data['total']) || 
    !isset($data['items']) || 
    !is_array($data['items']) || 
    !isset($data['payment_method']) ||
    !isset($data['name']) || !isset($data['address']) || !isset($data['phone'])
) {
    echo json_encode(["status" => "error", "message" => "Missing order data"]);
    exit;
}

$user_id = $data['user_id'];
$total = $data['total'];
$items = $data['items'];
$payment_method = $data['payment_method'];
$name = $data['name'];
$address = $data['address'];
$phone = $data['phone'];

// بيانات البطاقة
$card_number = isset($data['card_number']) ? $data['card_number'] : null;
$expiry_date = isset($data['expiry_date']) ? $data['expiry_date'] : null;

// ✅ إنشاء الطلب
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status, created_at, name, address, phone) VALUES (?, ?, 'pending', NOW(), ?, ?, ?)");
$stmt->bind_param("idsss", $user_id, $total, $name, $address, $phone);
if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error", 
        "message" => "❌ Failed to create order (orders table): " . $stmt->error
    ]);
    exit;
}
$order_id = $stmt->insert_id;
$stmt->close();

// ✅ إدخال الدفع
if ($payment_method === 'card' && $card_number && $expiry_date) {
    $last_four = substr($card_number, -4);
    $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, method, card_last_four, expiry_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $payment_stmt->bind_param("isss", $order_id, $payment_method, $last_four, $expiry_date);
} else {
    $payment_stmt = $conn->prepare("INSERT INTO payments (order_id, method, status) VALUES (?, ?, 'pending')");
    $payment_stmt->bind_param("is", $order_id, $payment_method);
}

if (!$payment_stmt->execute()) {
    echo json_encode([
        "status" => "error", 
        "message" => "❌ Failed to insert payment: " . $payment_stmt->error
    ]);
    exit;
}
$payment_stmt->close();

// ✅ إدخال العناصر
$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
$stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

foreach ($items as $item) {
    $product_id = $item['id'];
    $quantity = $item['quantity'];
    $price = $item['price'];

    $item_stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    if (!$item_stmt->execute()) {
        echo json_encode([
            "status" => "error", 
            "message" => "❌ Failed to insert order item: " . $item_stmt->error
        ]);
        exit;
    }

    $stock_stmt->bind_param("ii", $quantity, $product_id);
    if (!$stock_stmt->execute()) {
        echo json_encode([
            "status" => "error", 
            "message" => "❌ Failed to update stock: " . $stock_stmt->error
        ]);
        exit;
    }
}

$item_stmt->close();
$stock_stmt->close();

echo json_encode([
  "status" => "success",
  "message" => "Order placed successfully",
  "order_id" => $order_id  // ✅ ضروري!
]);



$conn->close();
