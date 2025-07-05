<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once("../config/db.php");

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode([
    "status" => "success",
    "products" => $products
]);
?>
