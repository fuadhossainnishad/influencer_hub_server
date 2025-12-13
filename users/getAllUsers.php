<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

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
            tiktok
        FROM users
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $users
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch users: " . $e->getMessage()
    ]);
}
?>