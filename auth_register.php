<?php
/**
 * User Registration Handler
 */

require_once 'config.php';
require_once 'auth_config.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

// Get form data
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validation
if (empty($username) || strlen($username) < 3) {
    sendJSONResponse(false, 'Username must be at least 3 characters');
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSONResponse(false, 'Valid email is required');
}

if (empty($password) || strlen($password) < 6) {
    sendJSONResponse(false, 'Password must be at least 6 characters');
}

// Connect to database
$conn = getDBConnection();

// Check if username exists
$check_sql = "SELECT id FROM users WHERE username = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "s", $username);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    mysqli_stmt_close($check_stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Username already exists');
}
mysqli_stmt_close($check_stmt);

// Check if email exists
$check_sql = "SELECT id FROM users WHERE email = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "s", $email);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    mysqli_stmt_close($check_stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Email already registered');
}
mysqli_stmt_close($check_stmt);

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Registration successful');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Registration failed: ' . mysqli_error($conn));
}
?>