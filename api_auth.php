<?php

// Enable CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight requests (CORS OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Display errors for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

 include("config.php");
 
// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

// Start session (ensures session ID is maintained)
session_start();

// Get JSON input data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input fields
if (empty($data["username"]) || empty($data["password"])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Username and password are required."]);
    exit();
}

// Clean user inputs
$username = trim($data["username"]);
$password = trim($data["password"]);

// Prepare SQL statement to check user credentials
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
    exit();
}

// Fetch user data
$user = $result->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($password, $user["password"])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Invalid username or password."]);
    exit();
}

// Store user details in session
$_SESSION["user_id"] = $user["id"];
$_SESSION["username"] = $user["username"];
$_SESSION["role"] = $user["role"];

// Set a cookie for authentication (optional)
setcookie("session_id", session_id(), time() + 3600, "/", "localhost", false, true);

// Successful login response
http_response_code(200);
echo json_encode(["status" => "success", "message" => "Login successful.", "role" => $user["role"]]);

// Close database connection
$conn->close();
?>
