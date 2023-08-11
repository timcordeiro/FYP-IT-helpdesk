<?php
// Check if a session is not already started and start it if not
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
include "lib/redirect.php"; // Redirect functions
include "lib/connection.php"; // Database connection
include "lib/csrf.php"; // Cross-Site Request Forgery protection

// Sanitize and get the "id" and "table" values from the POST data
$id = filter_var($_POST['id'], FILTER_SANITIZE_SPECIAL_CHARS);
$table = filter_var($_POST['table'], FILTER_SANITIZE_SPECIAL_CHARS);

// Check if the "id" value is empty, redirect if so
if (empty($id)) {
    redirect();
}

// Prepare and execute SQL statement to fetch the attachment data based on the specified table
if ($table == "tickets") {
    $stmt = $conn->prepare("SELECT attachment, file_extension, mime_type, timestamp FROM tickets WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    // Fetch all rows as an associative array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $name = $result[0]['timestamp'] . '.' . $result[0]['file_extension'];
} elseif ($table == "loa") {
    $stmt = $conn->prepare("SELECT attachment, filename, mime_type FROM loa WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    // Fetch all rows as an associative array
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $name = $result[0]['filename'];
}

// Get the attachment content and MIME type from the fetched result
$attachment = $result[0]['attachment'];
$mime_type = $result[0]['mime_type'];

// Close the database connection
sqlclose($conn);

// Set the appropriate headers for the file download
header("Content-Type: " . $mime_type);
header("Content-Disposition: attachment; filename=\"" . $name . "\"");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Clean output buffer and flush to ensure no output conflicts with the download content
ob_clean();
flush();

// Output the attachment content for download
echo $attachment;
?>
