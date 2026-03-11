<?php
/**
 * Get Genres/Categories
 */

require_once 'config.php';

setJSONHeader();

$conn = getDBConnection();

$sql = "SELECT id, name, color FROM genres ORDER BY name";
$result = mysqli_query($conn, $sql);

$genres = array();

while ($row = mysqli_fetch_assoc($result)) {
    $genres[] = array(
        'id' => $row['id'],
        'name' => $row['name'],
        'color' => $row['color']
    );
}

closeDBConnection($conn);

sendJSONResponse(true, 'Genres retrieved', ['genres' => $genres]);
?>