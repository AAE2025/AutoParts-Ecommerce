<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

require_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['review_id'], $data['user_id'], $data['role'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$review_id = intval($data['review_id']);
$user_id = intval($data['user_id']);
$role = $data['role'];

// 1. جلب صاحب المراجعة
$stmt = $conn->prepare("SELECT user_id FROM reviews WHERE id = ?");
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Review not found"]);
    exit;
}

$row = $result->fetch_assoc();
$review_owner_id = intval($row['user_id']);

// 2. التحقق من الصلاحيات
if ($review_owner_id !== $user_id && $role !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// 3. حذف المراجعة
$delete = $conn->prepare("DELETE FROM reviews WHERE id = ?");
$delete->bind_param("i", $review_id);

if ($delete->execute()) {
    echo json_encode(["status" => "success", "message" => "Review deleted successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete review"]);
}

$conn->close();
