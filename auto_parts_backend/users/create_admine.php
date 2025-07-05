<?php
include_once("../config/db.php");

$username = "Admin";
$email = "admin@admin.com";
$password = "admin123";

$hashed_password = password_hash($password, PASSWORD_DEFAULT);


$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo "⚠️ Admin with this email already exists.";
    $check->close();
    $conn->close();
    exit;
}
$check->close();


$stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo "✅ Admin created successfully.";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
