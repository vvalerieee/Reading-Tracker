<?php
/**
 * Get Reading Goals with Progress
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

$user_id = getCurrentUserId();
$conn = getDBConnection();

$response = ['success' => true];

// Get monthly goal
$year = date('Y');
$month = date('n');

$sql = "SELECT target_books, target_pages FROM reading_goals 
        WHERE user_id = ? AND goal_type = 'monthly' AND year = ? AND month = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $user_id, $year, $month);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Get current month progress
    $progress_sql = "SELECT 
                        COUNT(*) as books_count,
                        SUM(pages_read) as pages_count
                     FROM books 
                     WHERE user_id = ? 
                        AND YEAR(created_at) = ? 
                        AND MONTH(created_at) = ?";
    $prog_stmt = mysqli_prepare($conn, $progress_sql);
    mysqli_stmt_bind_param($prog_stmt, "iii", $user_id, $year, $month);
    mysqli_stmt_execute($prog_stmt);
    $prog_result = mysqli_stmt_get_result($prog_stmt);
    $progress = mysqli_fetch_assoc($prog_result);
    
    $response['monthly'] = [
        'target_books' => intval($row['target_books']),
        'target_pages' => intval($row['target_pages']),
        'current_books' => intval($progress['books_count']),
        'current_pages' => intval($progress['pages_count'] ?? 0)
    ];
    
    mysqli_stmt_close($prog_stmt);
}
mysqli_stmt_close($stmt);

// Get yearly goal
$sql = "SELECT target_books, target_pages FROM reading_goals 
        WHERE user_id = ? AND goal_type = 'yearly' AND year = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $year);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Get current year progress
    $progress_sql = "SELECT 
                        COUNT(*) as books_count,
                        SUM(pages_read) as pages_count
                     FROM books 
                     WHERE user_id = ? AND YEAR(created_at) = ?";
    $prog_stmt = mysqli_prepare($conn, $progress_sql);
    mysqli_stmt_bind_param($prog_stmt, "ii", $user_id, $year);
    mysqli_stmt_execute($prog_stmt);
    $prog_result = mysqli_stmt_get_result($prog_stmt);
    $progress = mysqli_fetch_assoc($prog_result);
    
    $response['yearly'] = [
        'target_books' => intval($row['target_books']),
        'target_pages' => intval($row['target_pages']),
        'current_books' => intval($progress['books_count']),
        'current_pages' => intval($progress['pages_count'] ?? 0)
    ];
    
    mysqli_stmt_close($prog_stmt);
}
mysqli_stmt_close($stmt);

closeDBConnection($conn);

echo json_encode($response);
?>