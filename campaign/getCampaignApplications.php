<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

// Auth check
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

// GET campaign_id from query param
$campaign_id = $_GET['campaign_id'] ?? null;
if (!$campaign_id) {
    echo json_encode(["success" => false, "message" => "campaign_id required"]);
    exit;
}

// Get applications
$stmt = $conn->prepare("SELECT user_id, status FROM campaign_applications WHERE campaign_id=?");
$stmt->execute([$campaign_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalApplied = count($applications);
$appliedByCurrentUser = false;

foreach ($applications as $app) {
    if ($app['user_id'] == $userId) {
        $appliedByCurrentUser = true;
        break;
    }
}

echo json_encode([
    "success" => true,
    "data" => [
        "total_applied" => $totalApplied,
        "applied_by_current_user" => $appliedByCurrentUser,
        "applications" => $applications
    ]
]);
?>
