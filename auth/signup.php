<?php
header('Content-Type: application/json');
require_once "../config/database.php";

$db = new Database();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->name) || 
    !isset($data->username) || 
    !isset($data->password) || 
    !isset($data->email)) {

    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

$name = $data->name;
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
        INSERT INTO users (name, username, email, password)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$name, $username, $email, $password]);

    echo json_encode([
        "success" => true, 
        "message" => "User registered successfully"
    ]);

} catch(PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
