<?php
/**
 * Update User Profile
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();
$username = isset($_POST['username']) ? sanitizeInput($_POST['username']) : '';
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';

// Validation
if (empty($username) || strlen($username) < 3) {
    sendJSONResponse(false, 'Username must be at least 3 characters');
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSONResponse(false, 'Valid email is required');
}

$conn = getDBConnection();

// Check if username is taken by another user
$check_sql = "SELECT id FROM users WHERE username = ? AND id != ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "si", $username, $user_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    mysqli_stmt_close($check_stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Username already taken');
}
mysqli_stmt_close($check_stmt);

// Check if email is taken by another user
$check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "si", $email, $user_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) > 0) {
    mysqli_stmt_close($check_stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Email already registered');
}
mysqli_stmt_close($check_stmt);

// Update profile
$sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Update session username
    $_SESSION['username'] = $username;
    
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Profile updated successfully');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Profile update failed');
}
?>