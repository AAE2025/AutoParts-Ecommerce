<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); 
header("Access-Control-Allow-Credentials: true");             
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include_once("../config/db.php");
header("Content-Type: application/json");

$sql = "SELECT orders.*, users.username AS customer_name FROM orders 
        LEFT JOIN users ON orders.user_id = users.id 
        ORDER BY orders.id DESC";

$result = $conn->query($sql);

$orders = [];

while ($row = $result->fetch_assoc()) {
    $order_id = $row['id'];

    // Fetch order items for each order
    $items_sql = "SELECT order_items.*, products.name AS product_name 
                  FROM order_items 
                  JOIN products ON order_items.product_id = products.id 
                  WHERE order_items.order_id = $order_id";

    $items_result = $conn->query($items_sql);
    $items = [];

    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    $row['items'] = $items;
    $orders[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $orders
]);

$conn->close();

