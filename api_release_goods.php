<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

error_reporting(E_ALL);
ini_set('display_errors', 1);

 include("config.php");

$sql = "SELECT id, unit, quantity, items_description FROM warehouse_goods";
$result = $conn->query($sql);

$goods = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $goods[] = $row;
    }
}

echo json_encode($goods);
$conn->close();
?>
