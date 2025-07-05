<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
include_once("../config/db.php");

$sql = "SELECT * FROM products WHERE is_featured = 1 LIMIT 8";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode(["status" => "success", "products" => $products]);
$conn->close();
?>
