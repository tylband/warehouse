<?php
// Enable CORS (Cross-Origin Resource Sharing) - Only allow your domain in production
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}
// Display errors for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

 include("config.php");
 
// Check request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
    exit();
}

// Get JSON or FormData input
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    $data = $_POST; // Fallback for form-encoded data
}

// Validate required fields
if (empty($data["username"]) || empty($data["firstname"]) || empty($data["lastname"]) || empty($data["password"])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

$username = trim($data["username"]);
$firstname = trim($data["firstname"]);
$lastname = trim($data["lastname"]);
$role = isset($data["role"]) ? trim($data["role"]) : "staff"; // Default role is 'staff'
$password = password_hash($data["password"], PASSWORD_BCRYPT); // Hash password

// Validate the role
$valid_roles = ["staff", "admin"];
if (!in_array($role, $valid_roles)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid role specified."]);
    exit();
}
// Check if username already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "Username already exists."]);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Insert new user with role
$stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $password, $firstname, $lastname, $role);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(["status" => "success", "message" => "Registration successful."]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Registration failed."]);
}

// Close connections
$stmt->close();
$conn->close();
?>
