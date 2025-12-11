<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success"=>false, "message"=>"user_id required"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT ca.id AS application_id, ca.campaign_id, ca.status, ca.applied_at,
               c.title, c.description, c.budget, c.thumbnail, c.created_at
        FROM campaign_applications ca
        JOIN campaigns c ON ca.campaign_id = c.id
        WHERE ca.user_id = ?
        ORDER BY ca.applied_at DESC
    ");
    $stmt->execute([$user_id]);
    $appliedCampaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $appliedCampaigns
    ]);
} catch(PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch applied campaigns: " . $e->getMessage()
    ]);
}
?>
