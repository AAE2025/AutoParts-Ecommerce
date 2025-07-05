<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
require_once("../config/db.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = intval($data["id"]);
$username = mysqli_real_escape_string($conn, $data["username"]);
$email = mysqli_real_escape_string($conn, $data["email"]);
$role = mysqli_real_escape_string($conn, $data["role"]);

$sql = "UPDATE users SET username='$username', email='$email', role='$role' WHERE id=$id";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["status" => "success", "message" => "User updated"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update user"]);
}
?>
