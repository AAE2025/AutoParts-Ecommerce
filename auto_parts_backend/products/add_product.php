<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include_once("../config/db.php");

$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$price = $_POST['price'] ?? '';
$stock = $_POST['stock'] ?? '';
$description = $_POST['description'] ?? '';
$imageName = null;

// ✅ رفع الصورة
if (isset($_FILES['image'])) {
    $targetDir = "../uploads/";
    $imageName = time() . '_' . basename($_FILES["image"]["name"]);
    $targetFile = $targetDir . $imageName;

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
        echo json_encode(["status" => "error", "message" => "Image upload failed"]);
        exit;
    }
}

$sql = "INSERT INTO products (name, category, price, stock, description, image)
        VALUES ('$name', '$category', '$price', '$stock', '$description', '$imageName')";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Product added successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
}

$conn->close();
