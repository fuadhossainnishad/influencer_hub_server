<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";
require_once "../db/campaignTable.php";


$db = new Database();
$conn = $db->connect();

// ------------------- AUTHENTICATION -------------------
$headers = getallheaders();
if (!isset($headers['Authorization'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$token = str_replace("Bearer ", "", $headers['Authorization']);
// TODO: Verify token from user table here

// ------------------- POST DATA -------------------
$title = $_POST['title'] ?? null;
$description = $_POST['description'] ?? null;
$goal = $_POST['goal'] ?? null;
$budget = $_POST['budget'] ?? null;
$timeline = $_POST['timeline'] ?? null;
$deliverables = $_POST['deliverables'] ?? null;

// Files
$thumbnail = $_FILES['thumbnail'] ?? null;
$creative = $_FILES['creative'] ?? null;

if (!$title || !$description || !$goal || !$budget || !$timeline || !$deliverables) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

// ------------------- CREATE UPLOAD FOLDERS IF NOT EXISTS -------------------
$thumbnailDir = "../uploads/thumbnails/";
$creativeDir = "../uploads/creative/";

if (!is_dir($thumbnailDir)) {
    mkdir($thumbnailDir, 0777, true);
}
if (!is_dir($creativeDir)) {
    mkdir($creativeDir, 0777, true);
}

// ------------------- UPLOAD FILES -------------------
$thumbnailPath = null;
if ($thumbnail && $thumbnail['error'] == 0) {
    $thumbnailPath = "uploads/thumbnails/" . uniqid() . "_" . basename($thumbnail['name']);
    move_uploaded_file($thumbnail['tmp_name'], "../" . $thumbnailPath);
}

$creativePath = null;
if ($creative && $creative['error'] == 0) {
    $creativePath = "uploads/creative/" . uniqid() . "_" . basename($creative['name']);
    move_uploaded_file($creative['tmp_name'], "../" . $creativePath);
}

try {
    $stmt = $conn->prepare("
        INSERT INTO campaigns 
        (title, description, goal, budget, timeline, deliverables, thumbnail, creative)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $goal, $budget, $timeline, $deliverables, $thumbnailPath, $creativePath]);

    echo json_encode(["success" => true, "message" => "Campaign created successfully"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
