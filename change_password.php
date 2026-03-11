<?php
/**
 * Change User Password
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();
$current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';

// Validation
if (empty($current_password) || empty($new_password)) {
    sendJSONResponse(false, 'All fields are required');
}

if (strlen($new_password) < 6) {
    sendJSONResponse(false, 'New password must be at least 6 characters');
}

$conn = getDBConnection();

// Verify current password
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!password_verify($current_password, $user['password'])) {
    closeDBConnection($conn);
    sendJSONResponse(false, 'Current password is incorrect');
}

// Update password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Password changed successfully');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Password change failed');
}
?>