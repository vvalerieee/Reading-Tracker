<?php
/**
 * Enhanced Get Books - Returns books for authenticated user only
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

$user_id = getCurrentUserId();

// Connect to database
$conn = getDBConnection();

// Get all books for this user
$sql = "SELECT id, title, author, total_pages, pages_read, genre, cover_image, 
               notes, rating, start_date, finish_date, created_at 
        FROM books 
        WHERE user_id = ? 
        ORDER BY created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$books = array();

while ($row = mysqli_fetch_assoc($result)) {
    $books[] = array(
        'id' => $row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'total_pages' => intval($row['total_pages']),
        'pages_read' => intval($row['pages_read']),
        'genre' => $row['genre'],
        'cover_image' => $row['cover_image'],
        'notes' => $row['notes'],
        'rating' => $row['rating'] ? intval($row['rating']) : null,
        'start_date' => $row['start_date'],
        'finish_date' => $row['finish_date'],
        'created_at' => $row['created_at']
    );
}

mysqli_stmt_close($stmt);
closeDBConnection($conn);

sendJSONResponse(true, 'Books retrieved', ['books' => $books]);
?>