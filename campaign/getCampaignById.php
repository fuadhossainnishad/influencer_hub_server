<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

$campaign_id = $_GET['campaign_id'] ?? null;

if (!$campaign_id) {
    echo json_encode(["success" => false, "message" => "campaign_id required"]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM campaigns WHERE id=? LIMIT 1");
$stmt->execute([$campaign_id]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    echo json_encode(["success" => false, "message" => "Campaign not found"]);
    exit;
}

echo json_encode(["success" => true, "data" => $campaign]);
?>
