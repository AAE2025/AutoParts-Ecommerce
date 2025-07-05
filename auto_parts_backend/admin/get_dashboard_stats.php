<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); // لازم تكتب الدومين الصحيح
header("Access-Control-Allow-Credentials: true"); // علشان يقرأ الكوكيز من الجهة التانية
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header("Content-Type: application/json");


session_start();
include_once("../config/db.php");

// 🛡️ فقط الأدمن
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$response = [];

$response['products'] = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$response['users'] = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$response['orders'] = $conn->query("SELECT COUNT(*) AS count FROM orders")->fetch_assoc()['count'];
$response['sales'] = $conn->query("SELECT SUM(total) AS sum FROM orders")->fetch_assoc()['sum'] ?? 0;

echo json_encode([
    "status" => "success",
    "data" => $response
]);

$conn->close();
