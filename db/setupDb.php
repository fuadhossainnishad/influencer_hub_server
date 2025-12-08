<?php
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

try {
    $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100),
            username VARCHAR(100) UNIQUE,
            email VARCHAR(150) UNIQUE,
            password VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS otps (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(150),
            otp VARCHAR(6),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
    ";

    $conn->exec($sql);

    echo "Database setup completed successfully!";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
