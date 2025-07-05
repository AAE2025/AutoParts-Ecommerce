<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once("../config/db.php");

$id = $_POST['id'];
$username = $_POST['username'];
$email = $_POST['email'];
$address = $_POST['address'] ?? null;

$imageName = null;
if (isset($_FILES['image'])) {
    $imageName = time() . "_" . $_FILES['image']['name'];
    $target = "../uploads/" . $imageName;
    move_uploaded_file($_FILES['image']['tmp_name'], $target);
}

$sql = "UPDATE users SET username = ?, email = ?, address = ?" . ($imageName ? ", image = ?" : "") . " WHERE id = ?";
$stmt = $conn->prepare($imageName ?
    "UPDATE users SET username=?, email=?, address=?, image=? WHERE id=?" :
    "UPDATE users SET username=?, email=?, address=? WHERE id=?");

if ($imageName) {
    $stmt->bind_param("ssssi", $username, $email, $address, $imageName, $id);
} else {
    $stmt->bind_param("sssi", $username, $email, $address, $id);
}

if ($stmt->execute()) {
    // ✅ جلب بيانات المستخدم بعد التحديث
    $userQuery = $conn->prepare("SELECT id, username, email, address, image, role FROM users WHERE id = ?");
    $userQuery->bind_param("i", $id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    $updatedUser = $userResult->fetch_assoc();

    echo json_encode(["status" => "success", "user" => $updatedUser]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed"]);
}

$conn->close();
