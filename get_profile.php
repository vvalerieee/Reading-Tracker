<?php
require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

$user_id = getCurrentUserId();
$conn = getDBConnection();

$sql = "SELECT id, username, email, dark_mode, profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    sendJSONResponse(true, 'Profile retrieved', [
        'user' => [
            'id' => $row['id'],
            'username' => $row['username'],
            'email' => $row['email'],
            'dark_mode' => (bool)$row['dark_mode'],
            'profile_picture' => $row['profile_picture']
        ]
    ]);
} else {
    sendJSONResponse(false, 'User not found');
}
?>