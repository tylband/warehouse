<?php
session_start(); // Initialize the session at the top
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin: http://192.168.100.100");
// Include database connection (ensure this file exists and contains correct DB connection)
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit();
    }

    // Prepare SQL query to check if the user exists
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // User found, check if the password matches
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Password matches, store user information in the session
            $_SESSION['username'] = $username;
            
            // Send success response and redirect URL for the dashboard
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'redirect_url' => 'https://infosys.malaybalaycity.gov.ph/beta/assistancemayors/dashboard.php' // You may want to handle this client-side
            ]);
        } else {
            // Invalid password
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        }
    } else {
        // User not found
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
    }
} else {
    // Invalid request method
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
