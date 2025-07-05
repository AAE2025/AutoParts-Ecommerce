<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->id)) {
    echo json_encode([
        "status" => "error",
        "message" => "User ID is required"
    ]);
    exit;
}

$id = $data->id;

$sql = "DELETE FROM users WHERE id = $id";
if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "User deleted"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to delete user"]);
}
?>
