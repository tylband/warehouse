<?php
// Database connection details
$servername = "192.168.10.248";
$username = "prroot"; // Change if necessary
$password = "ee20de"; // Change if necessary
$database = "warehouse_db"; // Change if necessary

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit;
}
?>
