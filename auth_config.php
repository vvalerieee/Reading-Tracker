<?php
/**
 * Authentication Configuration
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Check if user is authenticated
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get current user info
function getCurrentUser() {
    if (!isAuthenticated()) {
        return null;
    }
    
    $conn = getDBConnection();
    $user_id = getCurrentUserId();
    
    $sql = "SELECT id, username, email, dark_mode FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $user = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    
    return $user;
}

// Require authentication (redirect if not logged in)
function requireAuth() {
    if (!isAuthenticated()) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'authenticated' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }
}

// Set user session
function setUserSession($user_id, $username) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $username;
}

// Destroy user session
function destroyUserSession() {
    session_unset();
    session_destroy();
}
?>
