<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

session_start();
include_once("../config/db.php");

// ✅ التحقق من صلاحية الأدمن
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access"
    ]);
    exit;
}

// ✅ إحصائيات عامة
$stats = [
    "total_users" => 0,
    "total_products" => 0,
    "total_orders" => 0,
    "total_sales" => 0
];

// إجمالي المستخدمين
$res = $conn->query("SELECT COUNT(*) AS total FROM users");
if ($res && $row = $res->fetch_assoc()) {
    $stats["total_users"] = (int) $row["total"];
}

// إجمالي المنتجات
$res = $conn->query("SELECT COUNT(*) AS total FROM products");
if ($res && $row = $res->fetch_assoc()) {
    $stats["total_products"] = (int) $row["total"];
}

// إجمالي الطلبات
$res = $conn->query("SELECT COUNT(*) AS total FROM orders");
if ($res && $row = $res->fetch_assoc()) {
    $stats["total_orders"] = (int) $row["total"];
}

// إجمالي المبيعات باستخدام total_price
$res = $conn->query("SELECT SUM(total_price) AS total FROM orders");
if ($res && $row = $res->fetch_assoc()) {
    $stats["total_sales"] = (float) $row["total"];
}

// ✅ جلب جميع المستخدمين
$users = [];
$res = $conn->query("SELECT id, username AS name, email, role FROM users");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}

// ✅ جلب جميع المنتجات
$products = [];
$res = $conn->query("SELECT id, name, category, price, stock FROM products");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
}

// ✅ جلب أحدث 5 طلبات
$recent_orders = [];
$res = $conn->query("
    SELECT orders.id, users.username AS customer_name, orders.total_price, orders.status, orders.created_at 
    FROM orders 
    JOIN users ON orders.user_id = users.id 
    ORDER BY orders.created_at DESC 
    LIMIT 5
");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

// ✅ الإخراج النهائي
echo json_encode([
    "status" => "success",
    "data" => [
        "stats" => $stats,
        "users" => $users,
        "products" => $products,
        "recent_orders" => $recent_orders
    ]
]);

$conn->close();
?>

