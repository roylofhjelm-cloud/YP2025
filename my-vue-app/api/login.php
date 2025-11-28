<?php
session_start();
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["username"]) || !isset($input["password"])) {
  http_response_code(400);
  echo json_encode(["error" => "Missing credentials"]);
  exit();
}

$username = $input["username"];
$password = $input["password"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  http_response_code(401);
  echo json_encode(["error" => "User not found"]);
  exit();
}

/* NOTE: When creating users, store password with password_hash() */
if (!password_verify($password, $user["password"])) {
  http_response_code(401);
  echo json_encode(["error" => "Invalid password"]);
  exit();
}

$_SESSION["user"] = [
  "u_id" => $user["u_id"],
  "role_id" => $user["role_id"],
  "username" => $user["username"]
];

echo json_encode([
  "success" => true,
  "user" => $_SESSION["user"]
]);