<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ------------------- AUTH CHECK -------------------
$headers = getallheaders();

if (!isset($headers["Authorization"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers["Authorization"]);

$stmt = $conn->prepare("SELECT id FROM users WHERE token=? LIMIT 1");
$stmt->execute([$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["success" => false, "message" => "Invalid Token"]);
    exit;
}

$userId = $user["id"];

// ------------------- POST DATA -------------------
$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;
$goal = $_POST['goal'] ?? null;
$budget = $_POST['budget'] ?? null;
$timeline = $_POST['timeline'] ?? null;
$deliverables = $_POST['deliverables'] ?? null;

if (!$title || !$description || !$goal || !$budget || !$timeline || !$deliverables) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

// ------------------- UPLOAD DIRECTORIES -------------------
$thumbnailDir = "../uploads/thumbnails/";
$creativeDir = "../uploads/creative/";

if (!is_dir($thumbnailDir)) mkdir($thumbnailDir, 0777, true);
if (!is_dir($creativeDir)) mkdir($creativeDir, 0777, true);

// ------------------- UPLOAD FILES -------------------
$thumbnailPath = null;
if (!empty($_FILES['thumbnail']['name'])) {
    $thumbnailPath = "uploads/thumbnails/" . uniqid() . "_" . basename($_FILES['thumbnail']['name']);
    move_uploaded_file($_FILES['thumbnail']['tmp_name'], "../" . $thumbnailPath);
}

$creativePath = null;
if (!empty($_FILES['creative']['name'])) {
    $creativePath = "uploads/creative/" . uniqid() . "_" . basename($_FILES['creative']['name']);
    move_uploaded_file($_FILES['creative']['tmp_name'], "../" . $creativePath);
}

// ------------------- INSERT CAMPAIGN -------------------
try {
    $stmt = $conn->prepare("
        INSERT INTO campaigns 
        (user_id, title, description, goal, budget, timeline, deliverables, thumbnail, creative)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $userId,
        $title,
        $description,
        $goal,
        $budget,
        $timeline,
        $deliverables,
        $thumbnailPath,
        $creativePath,
    ]);

    echo json_encode(["success" => true, "message" => "Campaign created successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
