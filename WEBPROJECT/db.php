<?php
$host = 'localhost';
$dbname = 'contact_management';
$username = 'root'; // Change if necessary
$password = ''; // Change if necessary

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>