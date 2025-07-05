<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['product_id'], $data['user_id'], $data['rating'], $data['comment']) ||
    empty($data['rating']) || empty($data['comment'])
) {
    echo json_encode(["status" => "error", "message" => "Incomplete review data"]);
    exit;
}

$product_id = intval($data['product_id']);
$user_id = intval($data['user_id']);
$rating = intval($data['rating']);
$comment = trim($data['comment']);

// التحقق من صحة التقييم
if ($rating < 1 || $rating > 5) {
    echo json_encode(["status" => "error", "message" => "Rating must be between 1 and 5"]);
    exit;
}

// إدخال المراجعة في قاعدة البيانات
$stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $product_id, $user_id, $rating, $comment);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Review submitted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to submit review"]);
}

$stmt->close();
$conn->close();
