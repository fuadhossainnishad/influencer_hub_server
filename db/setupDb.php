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
        phone VARCHAR(50) NULL,
        location VARCHAR(255) NULL,
        business_category VARCHAR(255) NULL,
        bio TEXT NULL,
        instagram VARCHAR(255) NULL,
        facebook VARCHAR(255) NULL,
        youtube VARCHAR(255) NULL,
        tiktok VARCHAR(255) NULL,
        contact_person_name VARCHAR(100) NULL,
        contact_person_phone VARCHAR(50) NULL,
        contact_person_email VARCHAR(150) NULL,
        profile_image VARCHAR(255) NULL,
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
