<?php
/**
 * Check Authentication Status
 */

require_once 'auth_config.php';

setJSONHeader();

if (isAuthenticated()) {
    $user = getCurrentUser();
    sendJSONResponse(true, 'Authenticated', [
        'authenticated' => true,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'dark_mode' => (bool)$user['dark_mode']
        ]
    ]);
} else {
    sendJSONResponse(false, 'Not authenticated', [
        'authenticated' => false
    ]);
}
?>