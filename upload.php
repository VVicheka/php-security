<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Check if a file was selected
if (!isset($_FILES["fileToUpload"]) || $_FILES["fileToUpload"]["error"] == 4) {
    $_SESSION['upload_message'] = "Please select a file to upload.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}

// Connect to database
try {
    $db = new PDO('mysql:host=127.0.0.1;dbname=php_security', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['upload_message'] = "System error. Please try again later.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}

// Define upload directory
$target_dir = "uploads/";

// Create uploads directory if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Create a .htaccess file to prevent direct access
$htaccess_file = $target_dir . ".htaccess";
if (!file_exists($htaccess_file)) {
    $htaccess_content = "Deny from all\n";
    file_put_contents($htaccess_file, $htaccess_content);
}

// Generate a unique filename to prevent overwriting
$file_extension = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
$unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
$target_file = $target_dir . $unique_filename;

// Store the original filename for display purposes
$original_filename = basename($_FILES["fileToUpload"]["name"]);

$uploadOk = 1;
$imageFileType = $file_extension;

// Check if image file is a actual image or fake image
if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if ($check !== false) {
        $message = "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        $message = "File is not an image.";
        $uploadOk = 0;
    }
}

// Check file size (500KB limit)
if ($_FILES["fileToUpload"]["size"] > 500000) {
    $message = "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    $_SESSION['upload_message'] = $message ? $message : "Sorry, your file was not uploaded.";
    $_SESSION['message_type'] = "error";
} else {
    // Additional security: Verify file content matches extension
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES["fileToUpload"]["tmp_name"]);
    finfo_close($finfo);
    
    $allowed_mime_types = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/gif' => ['gif']
    ];
    
    $mime_match = false;
    foreach ($allowed_mime_types as $mime => $extensions) {
        if ($mime_type === $mime && in_array($imageFileType, $extensions)) {
            $mime_match = true;
            break;
        }
    }
    
    if (!$mime_match) {
        $_SESSION['upload_message'] = "File type doesn't match its extension.";
        $_SESSION['message_type'] = "error";
    } else {
        // If everything is ok, try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            // Store file info in database
            try {
                $stmt = $db->prepare("INSERT INTO uploaded_files (filename, original_name, file_path, user_id) 
                                    VALUES (:filename, :original_name, :file_path, :user_id)");
                $stmt->bindParam(':filename', $unique_filename);
                $stmt->bindParam(':original_name', $original_filename);
                $stmt->bindParam(':file_path', $target_file);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();
                
                $_SESSION['upload_message'] = "The file " . htmlspecialchars($original_filename) . " has been uploaded.";
                $_SESSION['message_type'] = "success";
            } catch (PDOException $e) {
                // If database insert fails, remove the uploaded file
                unlink($target_file);
                $_SESSION['upload_message'] = "Error saving file information.";
                $_SESSION['message_type'] = "error";
                error_log("Database error: " . $e->getMessage());
            }
        } else {
            $_SESSION['upload_message'] = "Sorry, there was an error uploading your file.";
            $_SESSION['message_type'] = "error";
        }
    }
}

// Redirect back to index page
header("Location: index.php");
exit();
?>