<?php
// CORS Headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

//  Start session
session_start();
header('Content-Type: application/json');
include_once("../config/db.php");

//  Read JSON input
$data = json_decode(file_get_contents("php://input"));

//  Validate input
if (!$data || !isset($data->username) || !isset($data->email) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Missing input data"]);
    exit;
}

$username = trim($data->username);
$email = trim($data->email);
$password = password_hash($data->password, PASSWORD_DEFAULT);
$role = 'user'; // default role

//  Check if email exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email already exists"]);
    exit;
}

//  Insert user
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $username, $email, $password, $role);

if ($stmt->execute()) {
    $newUser = [
        "id" => $stmt->insert_id,
        "username" => $username,
        "email" => $email,
        "role" => $role
    ];

    $_SESSION['user'] = $newUser;

    echo json_encode(["status" => "success", "user" => $newUser]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed"]);
}

$stmt->close();
$conn->close();
?>
