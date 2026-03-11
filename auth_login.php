<?php
/**
 * User Login Handler
 */

require_once 'config.php';
require_once 'auth_config.php';

setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

// Get form data
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Validation
if (empty($username) || empty($password)) {
    sendJSONResponse(false, 'Username and password are required');
}

// Connect to database
$conn = getDBConnection();

// Get user
$sql = "SELECT id, username, password FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Invalid username or password');
}

$user = mysqli_fetch_assoc($result);

// Verify password
if (!password_verify($password, $user['password'])) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Invalid username or password');
}

// Set session
setUserSession($user['id'], $user['username']);

mysqli_stmt_close($stmt);
closeDBConnection($conn);

sendJSONResponse(true, 'Login successful', [
    'user' => [
        'id' => $user['id'],
        'username' => $user['username']
    ]
]);
?>