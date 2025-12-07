<?php
require_once "config.php";

// Allow local dev and live domain
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    "http://localhost:8080",
    "http://127.0.0.1:8080",
    "https://yp2025.rf.gd",
    "http://yp2025.rf.gd",
];
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://yp2025.rf.gd");
}
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data["username"] ?? "");
$email = trim($data["email"] ?? "");
$password = trim($data["password"] ?? "");

if (strlen($username) < 3 || strlen($password) < 3) {
    http_response_code(400);
    echo json_encode(["error" => "Användarnamn och lösenord måste vara minst 3 tecken"]);
    exit;
}
if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Ogiltig e-post"]);
    exit;
}

try {
    // Check duplicate
    $check = $pdo->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
    $check->execute([$username]);
    if ($check->fetch()) {
        http_response_code(400);
        echo json_encode(["error" => "Användarnamnet är upptaget"]);
        exit;
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id) VALUES (?, ?, ?, 1)");
    $stmt->execute([$username, $email, $hashed]);

    echo json_encode(["success" => true, "user_id" => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Kunde inte registrera användare"]);
}
