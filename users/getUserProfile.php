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

$user_id = $_GET['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["success"=>false,"message"=>"user_id required"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            id,
            username,
            email,
            phone,
            location,
            business_category,
            profile_image,
            bio,
            instagram,
            facebook,
            youtube,
            tiktok,
            created_at
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success"=>false,"message"=>"User not found"]);
    } else {
        echo json_encode(["success"=>true,"data"=>$user]);
    }
} catch (PDOException $e) {
    echo json_encode(["success"=>false,"message"=>"Error: " . $e->getMessage()]);
}
?>
