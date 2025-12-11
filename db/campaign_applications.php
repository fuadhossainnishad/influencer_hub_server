<?php
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS campaign_applications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        campaign_id INT NOT NULL,
        user_id INT NOT NULL,
        status ENUM('applied','approved','rejected') DEFAULT 'applied',
        applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";

    $conn->exec($sql);
    echo json_encode([
        "success" => true,
        "message" => "campaign_applications table created successfully!"
    ]);
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to create table: " . $e->getMessage()
    ]);
}
?>
