<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
    exit();

// ------------------- AUTH -------------------
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);
$stmt = $conn->prepare("SELECT id FROM users WHERE token=? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(["success" => false, "message" => "Invalid Token"]);
    exit;
}
$userId = $user['id'];

// ------------------- POST DATA -------------------
$campaign_id = $_POST['campaign_id'] ?? null;
if (!$campaign_id) {
    echo json_encode(["success" => false, "message" => "campaign_id required"]);
    exit;
}

// ------------------- CREATE TABLE IF NOT EXISTS -------------------
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
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Failed to create applications table: " . $e->getMessage()]);
    exit;
}

// ------------------- CHECK IF ALREADY APPLIED -------------------
$stmt = $conn->prepare("SELECT * FROM campaign_applications WHERE campaign_id=? AND user_id=?");
$stmt->execute([$campaign_id, $userId]);
if ($stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "Already applied"]);
    exit;
}

// ------------------- INSERT APPLICATION -------------------
$stmt = $conn->prepare("INSERT INTO campaign_applications (campaign_id,user_id) VALUES (?,?)");
$stmt->execute([$campaign_id, $userId]);

echo json_encode(["success" => true, "message" => "Applied successfully"]);
?>