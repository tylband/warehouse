<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);

 include("config.php");

$sql = "SELECT item_id, quantity, accountable_person, remarks, release_date FROM release_details ORDER BY release_date DESC";
$result = $conn->query($sql);

// Debug: Check if the SQL query executed successfully
if (!$result) {
    die(json_encode(["error" => "SQL error: " . $conn->error]));
}

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

// Debug: Check if there are any results
if (empty($logs)) {
    die(json_encode(["message" => "No data found in release_details"]));
}

echo json_encode($logs);
$conn->close();
?>
