<?php
/**
 * Book Details Script
 * Retrieves details of a single book for editing
 */

require_once 'config.php';

// Set JSON response header
setJSONHeader();

// Get book ID from query string
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validation
if ($id <= 0) {
    sendJSONResponse(false, 'Invalid book ID');
}

// Connect to database
$conn = getDBConnection();

// Prepare SQL statement
$sql = "SELECT id, title, author, total_pages, pages_read, created_at 
        FROM books 
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    closeDBConnection($conn);
    sendJSONResponse(false, 'Database error: ' . mysqli_error($conn));
}

// Bind parameter
mysqli_stmt_bind_param($stmt, "i", $id);

// Execute statement
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if book exists
if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Book not found');
}

// Fetch book data
$book = mysqli_fetch_assoc($result);

// Format response
$book_data = array(
    'id' => $book['id'],
    'title' => $book['title'],
    'author' => $book['author'],
    'total_pages' => intval($book['total_pages']),
    'pages_read' => intval($book['pages_read']),
    'created_at' => $book['created_at']
);

// Close connections
mysqli_stmt_close($stmt);
closeDBConnection($conn);

// Send response
sendJSONResponse(true, 'Book retrieved successfully', ['book' => $book_data]);
?>