<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS')
    exit();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->otp) || !isset($data->password)) {
    echo json_encode(["success" => false, "message" => "Email, OTP, and new password required"]);
    exit;
}

$email = $data->email;
$otp = $data->otp;
$newPassword = $data->password;

try {
    // Verify OTP
    $stmt = $conn->prepare("SELECT * FROM otps WHERE email=? AND otp=?");
    $stmt->execute([$email, $otp]);
    $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$otpRecord) {
        echo json_encode(["success" => false, "message" => "Invalid OTP"]);
        exit;
    }

    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->execute([$hashedPassword, $email]);

    // Delete OTP after use
    $stmt = $conn->prepare("DELETE FROM otps WHERE email=?");
    $stmt->execute([$email]);

    echo json_encode(["success" => true, "message" => "Password reset successfully"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>