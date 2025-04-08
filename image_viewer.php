<?php
// Get the image path from the query parameter
if (!isset($_GET['img']) || empty($_GET['img'])) {
    header("Location: index.php");
    exit;
}

$image_path = $_GET['img'];

// Basic security check to ensure the path is within the uploads directory
if (strpos($image_path, 'uploads/') !== 0 || !file_exists($image_path)) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<head>
    <title>Image Viewer</title>
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
    </style>
</head>
<body>
    <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php'; ?>" class="back-button">Back</a>
    
    <div class="image-container">
        <img src="<?php echo htmlspecialchars($image_path); ?>" alt="Full size image">
    </div>
</body>
</html>