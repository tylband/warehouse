<?php
// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight request
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(204); // No Content
    exit;
}

 include("config.php");

// Handle PUT request
if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    $json = file_get_contents("php://input");
    $data = json_decode($json, true);

    // Validate required fields
    $requiredFields = ["id", "unit", "quantity", "quantity_purchased", "items_description", "unit_cost", "total_cost", "category", "location", "date_received"];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === "")) {
            http_response_code(400);
            echo json_encode(["error" => "Missing or empty field: " . $field]);
            exit;
        }
    }

    // Sanitize and typecast inputs
    $id = intval($data['id']);
    $unit = trim($data['unit']);
    $quantity = floatval($data['quantity']);
    $quantity_purchased = floatval($data['quantity_purchased']);
    $items_description = trim($data['items_description']);
    $unit_cost = floatval($data['unit_cost']);
    $total_cost = floatval($data['total_cost']);
    $category = trim($data['category']);
    $location = trim($data['location']);
    $date_received = trim($data['date_received']);

    // Prepare the SQL statement
    $stmt = $conn->prepare("UPDATE warehouse_goods 
                            SET unit = ?, quantity = ?, quantity_purchased = ?, items_description = ?, unit_cost = ?, total_cost = ?, category = ?, location = ?, date_received = ?  
                            WHERE id = ?");

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(["error" => "Prepare statement failed: " . $conn->error]);
        exit;
    }

    // Corrected binding types: "sddssdsssi"
    $stmt->bind_param("sddssdsssi", 
        $unit, 
        $quantity, 
        $quantity_purchased, 
        $items_description, 
        $unit_cost, 
        $total_cost, 
        $category, 
        $location, 
        $date_received, 
        $id
    );

    // Execute the update
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "✅ Record updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Update failed: " . $stmt->error]);
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
