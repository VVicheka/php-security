<?php
// secure_image.php - Script to securely serve images only to authenticated users
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Return a 403 Forbidden status
    header('HTTP/1.0 403 Forbidden');
    echo 'You must be logged in to view this image.';
    exit;
}

// Get the image filename from the query parameter
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.0 404 Not Found');
    echo 'Image not found.';
    exit;
}

$filename = $_GET['file'];

// Connect to database to verify the file belongs to the current user
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=php_security', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare a statement to check if the file exists and belongs to the current user
    $stmt = $db->prepare("SELECT * FROM uploaded_files WHERE filename = :filename AND user_id = :user_id");
    $stmt->bindParam(':filename', $filename);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // File doesn't exist or doesn't belong to this user
        header('HTTP/1.0 404 Not Found');
        echo 'Image not found or you do not have permission to view it.';
        exit;
    }
    
    $file_info = $stmt->fetch(PDO::FETCH_ASSOC);
    $file_path = 'uploads/' . $filename;
    
    // Verify the file actually exists
    if (!file_exists($file_path)) {
        header('HTTP/1.0 404 Not Found');
        echo 'Image file not found on server.';
        exit;
    }
    
    // Get file MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    // Verify it's an image
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($mime_type, $allowed_mime_types)) {
        header('HTTP/1.0 403 Forbidden');
        echo 'Invalid file type.';
        exit;
    }
    
    // Set the appropriate content type
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($file_path));
    
    // Disable caching
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    
    // Output the file
    readfile($file_path);
    exit;
    
} catch (PDOException $e) {
    // Log the error, don't expose it to users
    error_log("Database error: " . $e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    echo 'An error occurred while processing your request.';
    exit;
}
?>