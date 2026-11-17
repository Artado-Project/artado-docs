<?php
$host = ''; // Use 127.0.0.1 instead of localhost for better compatibility
$port = ''; // Default MySQL port
$dbname = '';
$username = '';
$password = ''; // Default XAMPP MySQL password is empty
try {
    // First, try to connect without database to create it if needed
    $pdo_temp = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
    $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Now connect to the database
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // More helpful error message
    $error_msg = "Database connection failed: " . $e->getMessage();
    $error_msg .= "<br><br><strong>Common solutions:</strong>";
    $error_msg .= "<br>1. Make sure XAMPP MySQL service is running";
    $error_msg .= "<br>2. Check if MySQL port 3306 is available";
    $error_msg .= "<br>3. Verify username and password in db.php";
    $error_msg .= "<br>4. Try accessing phpMyAdmin to test MySQL connection";
    
    die($error_msg);
}
?>
