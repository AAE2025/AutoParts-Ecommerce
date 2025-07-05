<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');
include_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);
$token = trim($data["token"]);
$newPassword = trim($data["password"]);

if (empty($token) || empty($newPassword)) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

// تحديث كلمة المرور
$stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
$stmt->bind_param("ss", $hashedPassword, $token);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Password updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid token"]);
}

