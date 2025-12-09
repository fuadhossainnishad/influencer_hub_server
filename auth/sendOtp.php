<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email)) {
    echo json_encode(["success" => false, "message" => "Email is required"]);
    exit;
}

$email = $data->email;
$otp = rand(100000, 999999);

try {
    // Delete old OTP
    $stmt = $conn->prepare("DELETE FROM otps WHERE email=?");
    $stmt->execute([$email]);

    // Insert new OTP
    $stmt = $conn->prepare("INSERT INTO otps (email, otp) VALUES (?, ?)");
    $stmt->execute([$email, $otp]);

    echo json_encode([
        "success" => true,
        "message" => "OTP sent successfully",
        "otp" => $otp // ⚠️ Remove this in production
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
