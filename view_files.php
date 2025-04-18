<!DOCTYPE html>
<html>
<head>
    <title>Uploaded Files</title>
</head>
<body>
    <div class="file-list">
        <h2>Uploaded Files</h2>
        
        <?php
        $target_dir = "uploads/";
        $files = scandir($target_dir);
        
        // Remove . and .. from the list
        $files = array_diff($files, array('.', '..'));

        // Sort files by modification time (oldest to newest)
        usort($files, function ($a, $b) use ($target_dir) {
            return filemtime($target_dir . $a) - filemtime($target_dir . $b);
        });
        
        if (count($files) > 0) {
            foreach ($files as $file) {
                $file_path = $target_dir . $file;
                $file_type = pathinfo($file_path, PATHINFO_EXTENSION);
                
                echo "<div class='file-item'>";
                echo "<strong>Filename:</strong> " . htmlspecialchars($file) . "<br>";
                echo "<strong>File type:</strong> " . strtoupper($file_type) . "<br>";
                echo "<strong>File size:</strong> " . round(filesize($file_path) / 1024, 2) . " KB<br>";
                echo "<strong>Uploaded:</strong> " . date("F d Y H:i:s.", filemtime($file_path)) . "<br>";
                
                // Show preview for images
                if (in_array($file_type, array('jpg', 'jpeg', 'png', 'gif'))) {
                    $viewer_url = 'image_viewer.php?img=' . urlencode($file_path);
                    echo "<a href='" . $viewer_url . "'>View</a><br>";
                    echo "<img src='" . htmlspecialchars($file_path) . "' class='file-preview'>";
                }
                
                echo "</div>";
            }
        } else {
            echo "<p>No files have been uploaded yet.</p>";
        }
        ?>
    </div>
</body>
</html>