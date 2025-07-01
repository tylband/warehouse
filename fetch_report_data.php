<?php
header('Content-Type: application/json');
require(__DIR__ . '/fpdf186/fpdf.php');

 include("config.php");
 
$data = json_decode(file_get_contents("php://input"), true);
$query = strtolower(trim($data['query']));

$response = "I didn't understand that question.";

if (strpos($query, "total items released") !== false) {
    $sql = "SELECT SUM(quantity) AS total FROM release_logs";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $response = "Total items released: " . $row['total'];
    }
} elseif (preg_match("/released on ([0-9]{4}-[0-9]{2}-[0-9]{2})/", $query, $matches)) {
    $date = $matches[1];
    $sql = "SELECT COUNT(*) AS count FROM release_logs WHERE release_date = '$date'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $response = "Total items released on $date: " . $row['count'];
    }
} elseif (strpos($query, "most released item") !== false) {
    $sql = "SELECT item_id, SUM(quantity) AS total FROM release_logs GROUP BY item_id ORDER BY total DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $response = "Most released item ID: " . $row['item_id'] . " with " . $row['total'] . " releases.";
    }
}

echo json_encode(["response" => $response]);
$conn->close();
?>
