<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];
$newPassword = password_hash($data['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
$stmt->bind_param("si", $newPassword, $id);

if ($stmt->execute()) {
   
    $userQuery = $conn->prepare("SELECT id, username, email, address, image, role FROM users WHERE id = ?");
    $userQuery->bind_param("i", $id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $updatedUser = $userResult->fetch_assoc();

    echo json_encode(["status" => "success", "user" => $updatedUser]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update password"]);
}

$conn->close();
