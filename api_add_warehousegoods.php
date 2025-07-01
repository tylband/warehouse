<?php
// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

 include("config.php");
// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read and decode JSON input safely
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON format"]);
        exit;
    }

    // Validate required fields
    $requiredFields = ["unit", "quantity", "items_description", "unit_cost", "total_cost", "category", "location"];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === "")) {
            http_response_code(400);
            echo json_encode(["error" => "Missing or empty field: " . $field]);
            exit;
        }
    }

    // Sanitize and typecast inputs
    $unit = trim($data['unit']);
    $quantity = isset($data['quantity']) ? floatval($data['quantity']) : 0.00;
    $quantity_purchased = $quantity; // Ensure quantity_purchased is the same as quantity
    $items_description = trim($data['items_description']);
    $unit_cost = isset($data['unit_cost']) ? floatval($data['unit_cost']) : 0.00;
    $total_cost = isset($data['total_cost']) ? floatval($data['total_cost']) : 0.00;
    $category = trim($data['category']);
    $location = trim($data['location']);

    // Handle date_received (optional, defaults to current timestamp)
    $date_received = isset($data['date_received']) && strtotime($data['date_received'])
        ? date('Y-m-d H:i:s', strtotime($data['date_received']))
        : date('Y-m-d H:i:s');

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO warehouse_goods (unit, quantity, quantity_purchased, items_description, unit_cost, total_cost, category, location, date_received) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
        exit;
    }

    // Bind parameters
    $stmt->bind_param("sddssdsss", 
        $unit, 
        $quantity, 
        $quantity_purchased, 
        $items_description,  
        $unit_cost, 
        $total_cost, 
        $category, 
        $location,
        $date_received
    );

    // Execute query
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["message" => "✅ Record added successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Insert failed: " . $stmt->error]);
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
