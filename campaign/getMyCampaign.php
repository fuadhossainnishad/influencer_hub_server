<?php
// -------------------- CORS & Headers --------------------
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// -------------------- Database --------------------
require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

// -------------------- Get Authorization Header --------------------
// Some servers pass Authorization differently
$authHeader = null;

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} else {
    // Fallback for getallheaders()
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    }
}

// If header is still missing, unauthorized
if (!$authHeader) {
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

// -------------------- Extract Token --------------------
$token = str_replace("Bearer ", "", $authHeader);

// -------------------- Validate Token --------------------
$stmt = $conn->prepare("SELECT id FROM users WHERE token=? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid Token"
    ]);
    exit;
}

$userId = $user["id"];

// -------------------- Fetch User Campaigns --------------------
$stmt = $conn->prepare("SELECT * FROM campaigns WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------- Return Response --------------------
echo json_encode([
    "success" => true,
    "data" => $campaigns
]);
