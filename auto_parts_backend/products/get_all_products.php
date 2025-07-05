<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
require_once("../config/db.php");

header('Content-Type: application/json');

// pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$offset = ($page - 1) * $limit;

// filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$min = isset($_GET['min']) ? floatval($_GET['min']) : 0;
$max = isset($_GET['max']) ? floatval($_GET['max']) : 0;
$in_stock = isset($_GET['in_stock']) && $_GET['in_stock'] == '1';

// base query
$where = "WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (name LIKE '%$search%' OR description LIKE '%$search%')";
}

if (!empty($category) && $category !== "all") {
    $category = mysqli_real_escape_string($conn, $category);
    $where .= " AND category = '$category'";
}

if ($min > 0) {
    $where .= " AND price >= $min";
}

if ($max > 0) {
    $where .= " AND price <= $max";
}

if ($in_stock) {
    $where .= " AND stock > 0";
}

// total count
$count_sql = "SELECT COUNT(*) AS total FROM products $where";
$count_result = mysqli_query($conn, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$total = $count_row['total'];

// fetch products
$sql = "SELECT * FROM products $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$products = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "products" => $products,
        "total" => $total
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No products found"
    ]);
}
?>
