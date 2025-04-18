<?php
// image_viewer.php - Enhanced version
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit;
}

// Get the image filename from the query parameter
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("Location: index.php");
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
        header("Location: index.php");
        exit;
    }
    
    $file_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Log the error, don't expose it to users
    error_log("Database error: " . $e->getMessage());
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image Viewer - <?php echo htmlspecialchars($file_info['original_name']); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: white;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
        }
        
        .image-container {
            max-width: 90%;
            max-height: 80vh;
            margin: 0 auto;
            text-align: center;
        }
        
        img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }
        
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-family: Arial, sans-serif;
            z-index: 1000;
            border: 1px solid white;
        }
        
        .back-button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }
        
        .image-title {
            margin-top: 10px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-button">Back</a>
    
    <div class="image-container">
        <!-- Use secure_image.php to serve the image -->
        <img src="secure_image.php?file=<?php echo htmlspecialchars($filename); ?>" alt="Full size image">
        <div class="image-title"><?php echo htmlspecialchars($file_info['original_name']); ?></div>
    </div>
</body>
</html>