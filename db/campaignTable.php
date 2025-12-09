<?php
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

try {
    $createTableSQL = "
    CREATE TABLE IF NOT EXISTS campaigns (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        goal TEXT NOT NULL,
        budget VARCHAR(50) NOT NULL,
        timeline INT NOT NULL,
        deliverables TEXT NOT NULL,
        thumbnail VARCHAR(255),
        creative VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    $conn->exec($createTableSQL);
    echo "Campaigns table is ready.\n";
} catch(PDOException $e) {
    echo "Failed to create table: " . $e->getMessage();
}
