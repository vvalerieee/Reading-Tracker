<?php
/**
 * Save Reading Goals
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();
$goal_type = isset($_POST['goal_type']) ? $_POST['goal_type'] : '';
$target_books = isset($_POST['target_books']) ? intval($_POST['target_books']) : null;
$target_pages = isset($_POST['target_pages']) ? intval($_POST['target_pages']) : null;

if (!in_array($goal_type, ['monthly', 'yearly'])) {
    sendJSONResponse(false, 'Invalid goal type');
}

$year = date('Y');
$month = ($goal_type === 'monthly') ? date('n') : null;

$conn = getDBConnection();

// Check if goal exists
$check_sql = "SELECT id FROM reading_goals WHERE user_id = ? AND goal_type = ? AND year = ? AND month " . ($month ? "= ?" : "IS NULL");
$check_stmt = mysqli_prepare($conn, $check_sql);

if ($month) {
    mysqli_stmt_bind_param($check_stmt, "isii", $user_id, $goal_type, $year, $month);
} else {
    mysqli_stmt_bind_param($check_stmt, "isi", $user_id, $goal_type, $year);
}

mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);
$exists = mysqli_stmt_num_rows($check_stmt) > 0;
mysqli_stmt_close($check_stmt);

if ($exists) {
    // Update existing goal
    $sql = "UPDATE reading_goals SET target_books = ?, target_pages = ? 
            WHERE user_id = ? AND goal_type = ? AND year = ? AND month " . ($month ? "= ?" : "IS NULL");
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($month) {
        mysqli_stmt_bind_param($stmt, "iiisii", $target_books, $target_pages, $user_id, $goal_type, $year, $month);
    } else {
        mysqli_stmt_bind_param($stmt, "iiisi", $target_books, $target_pages, $user_id, $goal_type, $year);
    }
} else {
    // Insert new goal
    $sql = "INSERT INTO reading_goals (user_id, goal_type, target_books, target_pages, year, month) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isiiii", $user_id, $goal_type, $target_books, $target_pages, $year, $month);
}

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Goal saved successfully');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Error saving goal');
}
?>