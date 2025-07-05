<?php

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
session_start();
require_once("../config/db.php");


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}


$query = "SELECT id, username AS name, email, role FROM users";
$result = mysqli_query($conn, $query);

$users = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "data" => $users
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch users"
    ]);
}
?>





