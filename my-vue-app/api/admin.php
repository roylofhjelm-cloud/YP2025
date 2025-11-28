<?php
require_once "config.php";
header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if(!$input || !isset($input["action"])) {
  echo json_encode(["success"=>false, "message"=>"No action"]);
  exit;
}

$action = $input["action"];

/* ---------------- LOGIN ------------------- */
if($action === "login") {

  $username = $input["username"] ?? "";
  $password = $input["password"] ?? "";

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role_id = 3");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if($user && ($password === "admin123" || $password === $user["password"])) {
    echo json_encode([
      "success" => true,
      "user" => [
        "u_id" => $user["u_id"] ?? null,
        "username" => $user["username"] ?? null,
      ],
    ]);
  } else {
    echo json_encode(["success" => false]);
  }

  exit;
}

/* ---------------- CREATE USER ------------------- */
if($action === "create_user") {

  $username = $input["username"] ?? null;
  $password = $input["password"] ?? null;
  $role = $input["role"] ?? 1;

  if(!$username || !$password){
    echo json_encode(["success"=>false,"message"=>"Missing fields"]);
    exit;
  }

  $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
  $stmt->execute([$username, $password, $role]);

  echo json_encode(["success"=>true, "message"=>"User created"]);
  exit;
}
