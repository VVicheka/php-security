<?php
session_start();

// Check if a file was selected
if(!isset($_FILES["fileToUpload"]) || $_FILES["fileToUpload"]["error"] == 4) {
    $_SESSION['upload_message'] = "Please select a file to upload.";
    $_SESSION['message_type'] = "error";
    header("Location: index.php");
    exit();
}

$target_dir = "uploads/";

// Create uploads directory if it doesn't exist
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
  $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
  if($check !== false) {
    $message = "File is an image - " . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    $message = "File is not an image.";
    $uploadOk = 0;
  }
}

// Check if file already exists
if (file_exists($target_file)) {
  $message = "Sorry, file already exists.";
  $uploadOk = 0;
}

// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
  $message = "Sorry, your file is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
  $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  $_SESSION['upload_message'] = $message ? $message : "Sorry, your file was not uploaded.";
  $_SESSION['message_type'] = "error";
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
    $_SESSION['upload_message'] = "The file ". htmlspecialchars(basename($_FILES["fileToUpload"]["name"])). " has been uploaded.";
    $_SESSION['message_type'] = "success";
  } else {
    $_SESSION['upload_message'] = "Sorry, there was an error uploading your file.";
    $_SESSION['message_type'] = "error";
  }
}

// Redirect back to index page
header("Location: index.php");
exit();
?>