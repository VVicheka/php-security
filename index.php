<?php
    $db = new PDO('mysql:host=127.0.0.1;dbname=php_security', 'root', '');

    session_start();

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // // Vulnerable to SQL injection
        // $query = "SELECT * FROM user WHERE email = '$email' AND password = '$password'";
        // $result = $db->query($query);

        // if ($result && $result->rowCount() > 0) {
        //     echo "Login successful! Welcome, " . htmlspecialchars($email) . "!";
        // } else {
        //     echo "Invalid email or password.";
        // }
        // Injection sql: Email: ' OR '1'='1, Password: ' OR '1'='1

        // Prepare the SQL statement to prevent SQL injection
        $stmt = $db->prepare("SELECT * FROM user WHERE email = :email AND password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();

        // Check if a user was found
        if ($stmt->rowCount() > 0) {
            $_SESSION['logged_in'] = true;
            $_SESSION['email'] = $email;
        } else {
            echo "Invalid email or password.";
        }
    }
?>

<html>
<head>
    <title>File Upload and Management</title>
</head>
<body>
    <?php
    // Check if user is logged in
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        // User is logged in, show the upload form
        echo "<h1>Welcome, " . htmlspecialchars($_SESSION['email']) . "!</h1>";
        ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            Select image to upload:
            <input type="file" name="fileToUpload" id="fileToUpload">
            <input type="submit" value="Upload Image" name="submit">
        </form>
        <p><a href="logout.php">Logout</a></p>

        <?php
        // Include the view_files.php script to display uploaded files
        include 'view_files.php';
        ?>
    <?php
    } else {
        // User is not logged in, show the login form
    ?>
        <h1>Login Form</h1>
        <form action="index.php" method="POST">
            Email: <input type="text" name="email" required>
            <br><br>
            Password: <input type="password" name="password" required>
            <br><br>
            <input type="submit" value="Login">
        </form>
    <?php
    }
    ?>
</body> 
</html>