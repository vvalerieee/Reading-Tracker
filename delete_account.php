<?php
/**
 * Delete User Account
 * WARNING: This permanently deletes the user and all their data
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();
$conn = getDBConnection();

// Get user's profile picture to delete
$sql = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Delete profile picture if exists
if ($user && $user['profile_picture'] && file_exists('uploads/' . $user['profile_picture'])) {
    unlink('uploads/' . $user['profile_picture']);
}

// Delete all book cover images
$sql = "SELECT cover_image FROM books WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($book = mysqli_fetch_assoc($result)) {
    if ($book['cover_image'] && file_exists('uploads/' . $book['cover_image'])) {
        unlink('uploads/' . $book['cover_image']);
    }
}
mysqli_stmt_close($stmt);

// Delete user (CASCADE will delete books and goals automatically)
$sql = "DELETE FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    
    // Destroy session
    destroyUserSession();
    
    sendJSONResponse(true, 'Account deleted successfully');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Account deletion failed');
}
?>