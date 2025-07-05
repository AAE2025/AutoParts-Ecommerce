<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
header('Content-Type: application/json');
include_once("../config/db.php");

$data = json_decode(file_get_contents("php://input"));

if (!$data || !isset($data->email) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid JSON data"]);
    exit;
}

$email = $data->email;
$password = $data->password;

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = [
        "id" => $user['id'],
        "username" => $user['username'],
        "email" => $user['email'],
        "role" => $user['role'],
        'image' => $user['image']
    ];

    echo json_encode(["status" => "success", "user" => $_SESSION['user']]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid email or password"]);
}

$stmt->close();
$conn->close();

