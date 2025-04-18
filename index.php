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

<!DOCTYPE html>
<html>
<head>
    <title>File Upload and Management</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
    // Check if user is logged in
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        // User is logged in, show the header with welcome message and logout button
        ?>
        <div class="header-container">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['email']); ?>!</h1>
            <form class="logout-form" method="POST" action="logout.php">
                <button class="logout-button" type="submit" name="logout">Logout</button>
            </form>
        </div>
        
        <!-- Display upload messages if they exist -->
        <?php if (isset($_SESSION['upload_message'])): ?>
            <div class="<?php echo isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error' ? 'error-message' : 'success-message'; ?>">
                <?php 
                    echo $_SESSION['upload_message']; 
                    // Clear the message after displaying it
                    unset($_SESSION['upload_message']);
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="upload-container">
            <form action="upload.php" method="post" enctype="multipart/form-data">
                <h2>Upload Files</h2>
                <p>Select image to upload:</p>
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Upload Image" name="submit">
            </form>
        </div>

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
            <label for="email">Email:</label>
            <input type="text" name="email" id="email" required>
            
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            
            <input type="submit" value="Login">
        </form>
    <?php
    }
    ?>
</body> 
</html>