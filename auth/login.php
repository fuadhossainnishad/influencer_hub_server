<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}


// include DB config
require_once "../config/database.php";

// connect DB
$database = new Database();
$conn = $database->connect();

// read incoming JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["email"]) || !isset($data["password"])) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit;
}

$emailOrUsername = $data["email"];
$password = $data["password"];

try {
    // find user by email or username
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :value OR username = :value LIMIT 1");
    $stmt->execute(["value" => $emailOrUsername]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit;
    }

    // verify password
    if (!password_verify($password, $user["password"])) {
        echo json_encode(["success" => false, "message" => "Wrong password"]);
        exit;
    }

    // generate secure token
    $token = bin2hex(random_bytes(32));

    $saveToken = $conn->prepare("UPDATE users SET token=? WHERE id=?");
    $saveToken->execute([$token, $user["id"]]);

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "data" => [
            "user" => [
                "id" => $user["id"],
                "username" => $user["username"],
                "email" => $user["email"]
            ],
            "token" => $token
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
