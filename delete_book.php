<?php
/**
 * Delete Book Script
 * Handles deleting a book from the database
 */

require_once 'config.php';

// Set JSON response header
setJSONHeader();

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

// Get book ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Validation
if ($id <= 0) {
    sendJSONResponse(false, 'Invalid book ID');
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

// Prepare delete SQL statement
$sql = "DELETE FROM books WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    closeDBConnection($conn);
    sendJSONResponse(false, 'Database error: ' . mysqli_error($conn));
}

// Bind parameter
mysqli_stmt_bind_param($stmt, "i", $id);

// Execute the statement
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Book deleted successfully');
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Error deleting book: ' . mysqli_error($conn));
}
?>