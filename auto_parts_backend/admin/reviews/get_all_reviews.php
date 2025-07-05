<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("../../config/db.php");

// ✅ استعلام SQL محدث لإرجاع صورة المستخدم
$query = "SELECT r.id, r.product_id, r.user_id, r.rating, r.comment, r.created_at, 
                 p.name AS product_name, 
                 u.username, u.image AS user_image
          FROM reviews r
          JOIN products p ON r.product_id = p.id
          JOIN users u ON r.user_id = u.id
          ORDER BY r.created_at DESC";

$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $conn->error
    ]);
    exit;
}

$reviews = [];

while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}

echo json_encode(["status" => "success", "reviews" => $reviews]);
$conn->close();

