<?php
header("Access-Control-Allow-Origin: http://localhost:3000"); 
header("Access-Control-Allow-Credentials: true");             
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include_once("../config/db.php");

$id = $_POST['id'] ?? '';
$name = $_POST['name'] ?? '';
$category = $_POST['category'] ?? '';
$price = $_POST['price'] ?? '';
$stock = $_POST['stock'] ?? '';
$description = $_POST['description'] ?? '';
$image = null;

// ✅ إذا في صورة جديدة
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $targetDir = "../uploads/";
    $imageName = time() . '_' . basename($_FILES["image"]["name"]);
    $targetFilePath = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
        $image = $imageName;

        // ✅ حذف الصورة القديمة
        $getOld = $conn->query("SELECT image FROM products WHERE id = $id");
        if ($getOld && $getOld->num_rows > 0) {
            $old = $getOld->fetch_assoc();
            $oldPath = $targetDir . $old['image'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload new image"]);
        exit;
    }
}

if ($image) {
    $sql = "UPDATE products SET name=?, category=?, price=?, stock=?, description=?, image=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddssi", $name, $category, $price, $stock, $description, $image, $id);
} else {
    $sql = "UPDATE products SET name=?, category=?, price=?, stock=?, description=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssddsi", $name, $category, $price, $stock, $description, $id);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Product updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update product"]);
}

$stmt->close();
$conn->close();
