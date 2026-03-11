<?php
/**
 * Update Book Script
 * Handles updating book information
 */

require_once 'config.php';

// Set JSON response header
setJSONHeader();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

// Get and validate form data
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
$author = isset($_POST['author']) ? sanitizeInput($_POST['author']) : '';
$total_pages = isset($_POST['total_pages']) ? intval($_POST['total_pages']) : 0;
$pages_read = isset($_POST['pages_read']) ? intval($_POST['pages_read']) : 0;

// Validation
if ($id <= 0) {
    sendJSONResponse(false, 'Invalid book ID');
}

if (empty($title)) {
    sendJSONResponse(false, 'Book title is required');
}

if (empty($author)) {
    sendJSONResponse(false, 'Author name is required');
}

if ($total_pages < 1) {
    sendJSONResponse(false, 'Total pages must be at least 1');
}

if ($pages_read < 0) {
    sendJSONResponse(false, 'Pages read cannot be negative');
}

if ($pages_read > $total_pages) {
    sendJSONResponse(false, 'Pages read cannot exceed total pages');
}

// Connect to database
$conn = getDBConnection();

// Check if book exists
$check_sql = "SELECT id FROM books WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_sql);
mysqli_stmt_bind_param($check_stmt, "i", $id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) === 0) {
    mysqli_stmt_close($check_stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Book not found');
}

mysqli_stmt_close($check_stmt);

// Prepare update SQL statement
$sql = "UPDATE books SET title = ?, author = ?, total_pages = ?, pages_read = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    closeDBConnection($conn);
    sendJSONResponse(false, 'Database error: ' . mysqli_error($conn));
}

// Bind parameters
mysqli_stmt_bind_param($stmt, "ssiii", $title, $author, $total_pages, $pages_read, $id);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Book updated successfully');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Error updating book: ' . mysqli_error($conn));
}
?>