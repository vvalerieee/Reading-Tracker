<?php
/**
 * Upload Profile Picture
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    sendJSONResponse(false, 'No file uploaded');
}

$user_id = getCurrentUserId();
$allowed = ['jpg', 'jpeg', 'png', 'gif'];
$filename = $_FILES['profile_picture']['name'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    sendJSONResponse(false, 'Invalid file type. Only JPG, PNG, GIF allowed');
}

if ($_FILES['profile_picture']['size'] > 2 * 1024 * 1024) {
    sendJSONResponse(false, 'File too large. Max 2MB');
}

// Create uploads directory if not exists
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

// Delete old profile picture
$conn = getDBConnection();
$sql = "SELECT profile_picture FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && $user['profile_picture'] && file_exists('uploads/' . $user['profile_picture'])) {
    unlink('uploads/' . $user['profile_picture']);
}
mysqli_stmt_close($stmt);

// Upload new picture
$new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
$upload_path = 'uploads/' . $new_filename;

if (!move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
    closeDBConnection($conn);
    sendJSONResponse(false, 'File upload failed');
}

// Update database
$sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $new_filename, $user_id);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Profile picture uploaded', ['filename' => $new_filename]);
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Database update failed');
}
?>