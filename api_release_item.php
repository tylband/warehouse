<?php
header("Access-Control-Allow-Origin: *"); // Allow all origins (Replace '*' with your frontend URL in production)
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST & OPTIONS requests
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow expected headers
header('Content-Type: application/json');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

 include("config.php");

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4");

    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['id'], $data['quantity'], $data['accountable_person'], $data['remarks'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid request"]);
        exit;
    }

    // Sanitize and validate input
    $item_id = filter_var($data['id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
    $accountable_person = trim($data['accountable_person']);
    $remarks = trim($data['remarks']);

    if (!$item_id || !$quantity || $quantity <= 0 || empty($accountable_person)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid input values"]);
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Check stock availability
    $stmt = $conn->prepare("SELECT quantity FROM warehouse_goods WHERE id = ?");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["error" => "Item not found"]);
        exit;
    }

    $row = $result->fetch_assoc();
    $currentQuantity = (int)$row['quantity'];

    if ($currentQuantity < $quantity) {
        http_response_code(400);
        echo json_encode(["error" => "Not enough stock"]);
        exit;
    }

    // Update the stock quantity
    $newQuantity = $currentQuantity - $quantity;
    $updateStmt = $conn->prepare("UPDATE warehouse_goods SET quantity = ? WHERE id = ?");
    $updateStmt->bind_param("ii", $newQuantity, $item_id);
    $updateStmt->execute();

    // Insert release details
    $insertStmt = $conn->prepare("INSERT INTO release_details (item_id, quantity, accountable_person, remarks, release_date) VALUES (?, ?, ?, ?, NOW())");
    $insertStmt->bind_param("iiss", $item_id, $quantity, $accountable_person, $remarks);
    $insertStmt->execute();

    // Commit transaction
    $conn->commit();

    http_response_code(200);
    echo json_encode(["success" => "Item released successfully"]);

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} finally {
    $conn->close();
}
?>
