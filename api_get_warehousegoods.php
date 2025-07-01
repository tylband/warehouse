<?php
// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

 include("config.php");
 
// Fetch all warehouse goods including quantity_purchased
$sql = "SELECT id, unit, quantity, quantity_purchased, items_description, unit_cost, total_cost, category, location, date_received 
        FROM warehouse_goods 
        ORDER BY id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $warehouse_goods = [];
    while ($row = $result->fetch_assoc()) {
        $warehouse_goods[] = $row;
    }
    echo json_encode($warehouse_goods);
} else {
    echo json_encode([]);
}

// Close connection
$conn->close();
?>