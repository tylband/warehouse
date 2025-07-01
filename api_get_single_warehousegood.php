<?php
// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

 include("config.php");

// Check if ID is provided
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid ID format"]);
    exit;
}

$id = intval($_GET["id"]);

// Prepare and execute query
$stmt = $conn->prepare("SELECT * FROM warehouse_goods WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    http_response_code(404);
    echo json_encode(["error" => "Warehouse good not found"]);
}

$stmt->close();
$conn->close();
?>
