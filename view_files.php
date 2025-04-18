<?php
// view_files.php - Enhanced version
// This file should be included from index.php where session is already started

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    exit; // Don't show anything if not logged in
}

// Connect to database
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=php_security', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get files uploaded by the current user
    $stmt = $db->prepare("SELECT * FROM uploaded_files WHERE user_id = :user_id ORDER BY upload_date DESC");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo '<div class="file-list">';
    echo '<h2>Your Uploaded Files</h2>';
    
    if (count($files) > 0) {
        foreach ($files as $file) {
            echo "<div class='file-item'>";
            echo "<strong>Filename:</strong> " . htmlspecialchars($file['original_name']) . "<br>";
            echo "<strong>File type:</strong> " . strtoupper(pathinfo($file['original_name'], PATHINFO_EXTENSION)) . "<br>";
            
            // Get file size
            $file_path = 'uploads/' . $file['filename'];
            if (file_exists($file_path)) {
                echo "<strong>File size:</strong> " . round(filesize($file_path) / 1024, 2) . " KB<br>";
            }
            
            echo "<strong>Uploaded:</strong> " . $file['upload_date'] . "<br>";
            
            // Show preview for images using our secure viewer
            $file_extension = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION));
            if (in_array($file_extension, array('jpg', 'jpeg', 'png', 'gif'))) {
                $viewer_url = 'image_viewer.php?file=' . urlencode($file['filename']);
                echo "<a href='" . htmlspecialchars($viewer_url) . "'>View Full Image</a><br>";
                
                // Display thumbnail through secure_image.php
                echo "<img src='secure_image.php?file=" . htmlspecialchars($file['filename']) . "' class='file-preview'>";
            }
            
            echo "</div>";
        }
    } else {
        echo "<p>You haven't uploaded any files yet.</p>";
    }
    
    echo '</div>';
    
} catch (PDOException $e) {
    // Log the error, don't expose it to users
    error_log("Database error: " . $e->getMessage());
    echo "<p>An error occurred while retrieving your files.</p>";
}
?>