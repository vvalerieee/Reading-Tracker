<?php
/**
 * Enhanced Add Book Handler with Image Upload
 */

require_once 'config.php';
require_once 'auth_config.php';

requireAuth();
setJSONHeader();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

$user_id = getCurrentUserId();

// Get form data
$title = isset($_POST['title']) ? sanitizeInput($_POST['title']) : '';
$author = isset($_POST['author']) ? sanitizeInput($_POST['author']) : '';
$total_pages = isset($_POST['total_pages']) ? intval($_POST['total_pages']) : 0;
$pages_read = isset($_POST['pages_read']) ? intval($_POST['pages_read']) : 0;
$genre = isset($_POST['genre']) ? sanitizeInput($_POST['genre']) : null;
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : null;
$notes = isset($_POST['notes']) ? sanitizeInput($_POST['notes']) : null;
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$finish_date = isset($_POST['finish_date']) ? $_POST['finish_date'] : null;

// Validation
if (empty($title) || empty($author)) {
    sendJSONResponse(false, 'Title and author are required');
}

if ($total_pages < 1 || $pages_read < 0 || $pages_read > $total_pages) {
    sendJSONResponse(false, 'Invalid page numbers');
}

// Handle file upload
$cover_image = null;
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['cover_image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        sendJSONResponse(false, 'Invalid file type. Only JPG, PNG, GIF allowed');
    }
    
    if ($_FILES['cover_image']['size'] > 2 * 1024 * 1024) {
        sendJSONResponse(false, 'File too large. Max 2MB');
    }
    
    // Create uploads directory if not exists
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $cover_image = uniqid() . '_' . $filename;
    $upload_path = 'uploads/' . $cover_image;
    
    if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_path)) {
        sendJSONResponse(false, 'File upload failed');
    }
}

// Connect to database
$conn = getDBConnection();

// Insert book
$sql = "INSERT INTO books (user_id, title, author, total_pages, pages_read, genre, cover_image, notes, rating, start_date, finish_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "issiisssiss", $user_id, $title, $author, $total_pages, $pages_read, $genre, $cover_image, $notes, $rating, $start_date, $finish_date);

if (mysqli_stmt_execute($stmt)) {
    $book_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(true, 'Book added successfully', ['book_id' => $book_id]);
} else {
    mysqli_stmt_close($stmt);
    closeDBConnection($conn);
    sendJSONResponse(false, 'Error adding book');
}
?>