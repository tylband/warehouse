<?php
// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

 include("config.php");

if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing ID"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM warehouse_goods WHERE id = ?");
    $stmt->bind_param("i", $data['id']);

    if ($stmt->execute()) {
        echo json_encode(["message" => "✅ Record deleted successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to delete record"]);
    }

    $stmt->close();
}

$conn->close();
?>
    