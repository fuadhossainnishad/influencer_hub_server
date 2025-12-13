<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header('Content-Type: application/json');

require_once "../config/database.php";
$db = new Database();
$conn = $db->connect();

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$user_id = $_POST['id'] ?? null;
if (!$user_id) {
    echo json_encode(["success" => false, "message" => "User ID required"]);
    exit;
}

// Handle profile image
$profile_image = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $newFileName = 'profile_' . $user_id . '.' . $fileExtension;
    $uploadDir = '../uploads/profile_images/';
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
    $destPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $profile_image = "uploads/profile_images/" . $newFileName;
    }
}

try {
    // All fields to update
    $fields = [
        'username','email','phone','location','business_category','bio',
        'instagram','facebook','youtube','tiktok',
        'contact_person_name','contact_person_phone','contact_person_email'
    ];

    $update_sql = [];
    $params = [];

    foreach ($fields as $field) {
        // Use array_key_exists to allow empty strings
        if (array_key_exists($field, $_POST)) {
            $update_sql[] = "$field = :$field";
            $params[":$field"] = $_POST[$field];
        }
    }

    // Add profile image if uploaded
    if ($profile_image) {
        $update_sql[] = "profile_image = :profile_image";
        $params[":profile_image"] = $profile_image;
    }

    $params[":id"] = $user_id;

    if (!empty($update_sql)) {
        $stmt = $conn->prepare("UPDATE users SET " . implode(", ", $update_sql) . " WHERE id = :id");
        $stmt->execute($params);
        echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No data to update"]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
?>
