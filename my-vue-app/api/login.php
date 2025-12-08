<?php
// Admin login endpoint: verifies credentials, issues session + CSRF token.
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(["error" => "Method not allowed"]);
  exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$username = trim($input["username"] ?? "");
$password = trim($input["password"] ?? "");

if (strlen($username) < 3 || strlen($password) < 3) {
  http_response_code(400);
  echo json_encode(["error" => "Missing credentials"]);
  exit();
}

$stmt = $pdo->prepare("SELECT u_id, username, password, role_id FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user["password"])) {
  http_response_code(401);
  echo json_encode(["error" => "Invalid credentials"]);
  exit();
}

session_regenerate_id(true);
$_SESSION["user_id"] = $user["u_id"];
$_SESSION["role"] = $user["role_id"] == 3 ? "admin" : "student";
$csrf = issue_csrf();

echo json_encode([
  "success" => true,
  "user" => [
    "u_id" => $user["u_id"],
    "username" => $user["username"],
    "role_id" => $user["role_id"],
  ],
  "csrf_token" => $csrf
]);
