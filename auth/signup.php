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

if (
    !isset($data->username) ||
    !isset($data->password) ||
    !isset($data->email)
) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

$username = $data->username;
$password = password_hash($data->password, PASSWORD_BCRYPT);
$email = $data->email;

// Check if email already exists
$check = $conn->prepare("SELECT * FROM users WHERE email=? OR username=?");
$check->execute([$email, $username]);

if ($check->rowCount() > 0) {
    echo json_encode(["success" => false, "message" => "User already exists"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO users ( username, email, password)
        VALUES ( ?, ?, ?)
    ");
    $stmt->execute([$username, $email, $password]);

    echo json_encode([
        "success" => true,
        "message" => "User registered successfully"
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
