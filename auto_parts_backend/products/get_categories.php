<?php
header("Access-Control-Allow-Origin: *");
require_once("../config/db.php");
header('Content-Type: application/json');

$sql = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''";
$result = mysqli_query($conn, $sql);

$categories = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row['category'];
    }
    echo json_encode([
        "status" => "success",
        "categories" => $categories
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No categories found"
    ]);
}
?>


