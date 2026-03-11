<?php
/**
 * User Logout Handler
 */

require_once 'auth_config.php';

destroyUserSession();

setJSONHeader();
sendJSONResponse(true, 'Logged out successfully');
?>