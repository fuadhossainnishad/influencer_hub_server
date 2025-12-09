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

if (!isset($data->email) || !isset($data->otp)) {
    echo json_encode(["success" => false, "message" => "Email & OTP required"]);
    exit;
}

$email = $data->email;
$otp = $data->otp;

$stmt = $conn->prepare("SELECT * FROM otps WHERE email=? AND otp=?");
$stmt->execute([$email, $otp]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    echo json_encode(["success" => true, "message" => "OTP verified"]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid OTP"]);
}
