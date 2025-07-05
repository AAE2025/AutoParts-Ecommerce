<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("../config/db.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid product ID"]);
    exit;
}

$product_id = intval($_GET['id']);


$product_stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

if ($product_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Product not found"]);
    exit;
}

$product = $product_result->fetch_assoc();
$product_stmt->close();

 
$reviews_stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    LEFT JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$reviews_stmt->bind_param("i", $product_id);
$reviews_stmt->execute();
$reviews_result = $reviews_stmt->get_result();

$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
$reviews_stmt->close();

$related_stmt = $conn->prepare("
    SELECT * FROM products 
    WHERE category = ? AND id != ? 
    LIMIT 4
");
$related_stmt->bind_param("si", $product['category'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();

$related = [];
while ($row = $related_result->fetch_assoc()) {
    $related[] = $row;
}
$related_stmt->close();


echo json_encode([
    "status" => "success",
    "product" => $product,
    "reviews" => $reviews,
    "related" => $related
]);

$conn->close();
