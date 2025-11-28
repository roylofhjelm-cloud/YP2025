<?php
require_once "config.php";

header('Content-Type: application/json; charset=utf-8');

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data["username"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if (!$username || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Missing fields"]);
    exit;
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert into DB
$stmt = $db->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 1)");
try {
    $stmt->execute([$username, $email, $hashed]);
    echo json_encode(["success" => true, "user_id" => $db->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Could not register user"]);
}
?>
