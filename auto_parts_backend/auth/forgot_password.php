<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header('Content-Type: application/json');

include_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->email)) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

$email = $data->email;

// تحقق من وجود المستخدم
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode([
  "status" => "error",
  "message" => "Email not found"
]);

    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];
$token = bin2hex(random_bytes(32));

// احفظ التوكن في قاعدة البيانات
$updateStmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE id = ?");
$updateStmt->bind_param("si", $token, $userId);
$updateStmt->execute();

// في مشروع حقيقي هنا هنبعت إيميل. دلوقتي نعرض الرابط فقط
$resetLink = "http://localhost:3000/reset-password?token=" . $token;

echo json_encode([
  "status" => "success",
  "message" => "Reset email sent"
]);


$conn->close();
