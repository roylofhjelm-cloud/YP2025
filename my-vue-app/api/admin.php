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

// Read JSON body if present
$raw = file_get_contents("php://input");
$input = json_decode($raw, true);
if (!is_array($input)) {
  $input = [];
}

// Get action from either GET or JSON
$action = $_GET["action"] ?? ($input["action"] ?? null);

/*
  If action is missing, try to infer it (just in case).
  - username + password + role -> create_user
  - username + password        -> login
*/
if (!$action) {
  if (isset($input["username"], $input["password"], $input["role"])) {
    $action = "create_user";
  } elseif (isset($input["username"], $input["password"])) {
    $action = "login";
  }
}

// If still no action, bail
if (!$action) {
  echo json_encode(["success" => false, "message" => "No action"]);
  exit;
}

/* ---------------- ADMIN LOGIN (YOUR ORIGINAL LOGIC) ------------------- */
if ($action === "login") {

  $username = $input["username"] ?? "";
  $password = $input["password"] ?? "";

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role_id = 3");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // allow legacy plaintext match, admin123 override, or hashed password
  $validPassword = false;
  if ($user) {
    $validPassword =
      $password === "admin123" ||
      $password === ($user["password"] ?? "") ||
      password_verify($password, $user["password"] ?? "");
  }

  if ($user && $validPassword) {
    echo json_encode([
      "success" => true,
      "user" => [
        "u_id" => $user["u_id"],
        "username" => $user["username"],
      ],
    ]);
  } else {
    echo json_encode(["success" => false]);
  }

  exit;
}


/* ---------------- CREATE USER (YOUR ORIGINAL LOGIC) ------------------- */
if ($action === "create_user") {

  $username = $input["username"] ?? null;
  $password = $input["password"] ?? null;
  $email    = $input["email"]    ?? null;
  $role     = isset($input["role"]) ? intval($input["role"]) : 1; // default student

  if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
  }

  // keep roles within known bounds (1 student, 2 teacher, 3 admin)
  $role = in_array($role, [1, 2, 3]) ? $role : 1;

  $hashed = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("
    INSERT INTO users (username, password, email, role_id)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([$username, $hashed, $email, $role]);

  echo json_encode([
    "success" => true,
    "message" => "User created",
    "id" => $pdo->lastInsertId(),
  ]);
  exit;
}


/* ---------------- LIST USERS (FOR ADMIN DASHBOARD) ------------------- */
/* supports both:
   - POST { action: "users" }
   - GET  ?action=users
*/
if ($action === "users" || $action === "list_users") {
  try {
    $stmt = $pdo->query("
      SELECT u.u_id, u.username, u.email, u.xp, r.role_name
      FROM users u
      LEFT JOIN roles r ON u.role_id = r.role_id
      ORDER BY u.u_id DESC
    ");

    echo json_encode([
      "success" => true,
      "users"   => $stmt->fetchAll(PDO::FETCH_ASSOC),
    ]);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
  }
  exit;
}

echo json_encode(["success" => false, "message" => "Invalid action"]);
