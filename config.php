<?php
/**
 * Database Configuration File
 * This file contains database connection settings
 */

// Database credentials
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username (default for XAMPP)
define('DB_PASS', '');               // Database password (empty for XAMPP)
define('DB_NAME', 'reading_tracker'); // Database name

// Create database connection
function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Set charset to UTF-8
    mysqli_set_charset($conn, "utf8mb4");
    
    return $conn;
}

// Close database connection
function closeDBConnection($conn) {
    if ($conn) {
        mysqli_close($conn);
    }
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Set JSON header for API responses
function setJSONHeader() {
    header('Content-Type: application/json');
}

// Send JSON response
function sendJSONResponse($success, $message = '', $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}
?>