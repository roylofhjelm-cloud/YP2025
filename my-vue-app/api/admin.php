<?php
// Admin API: handles admin login plus CRUD for users, exercises, and materials (role-protected).
require_once "config.php";
require_once "auth_helpers.php";

// Allow local dev and live domain
allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
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

  if (strlen($username) < 3 || strlen($password) < 3) {
    echo json_encode(["success" => false, "message" => "Ogiltiga uppgifter"]);
    exit;
  }

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role_id = 3");
  $stmt->execute([$username]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  // Only accept hashed passwords
  $validPassword = $user && password_verify($password, $user["password"] ?? "");

  if ($user && $validPassword) {
    session_regenerate_id(true);
    $_SESSION["user_id"] = $user["u_id"];
    $_SESSION["role"] = "admin";
    $csrf = issue_csrf();
    echo json_encode([
      "success" => true,
      "user" => [
        "u_id" => $user["u_id"],
        "username" => $user["username"],
      ],
      "csrf_token" => $csrf,
    ]);
  } else {
    echo json_encode(["success" => false]);
  }

  exit;
}


/* ---------------- CREATE USER (YOUR ORIGINAL LOGIC) ------------------- */
if ($action === "create_user") {
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Bad CSRF"]);
    exit;
  }
  require_role("admin");

  $username = $input["username"] ?? ($_GET["username"] ?? null);
  $password = $input["password"] ?? ($_GET["password"] ?? null);
  $email    = isset($input["email"]) ? trim($input["email"]) : (isset($_GET["email"]) ? trim($_GET["email"]) : null);
  $role     = isset($input["role"]) ? intval($input["role"]) : (isset($_GET["role"]) ? intval($_GET["role"]) : 1); // default student

  if (!$username || !$password) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
  }

  // normalize email
  if ($email === "") {
    $email = null;
  }

  // keep roles within known bounds (1 student, 2 teacher, 3 admin)
  $role = in_array($role, [1, 2, 3]) ? $role : 1;

  $hashed = password_hash($password, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("
    INSERT INTO users (username, password, email, role_id)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->execute([$username, $hashed, $email, $role]);

  $newId = $pdo->lastInsertId();

  // fetch the freshly created user to echo back what was stored
  $created = null;
  try {
    $fetch = $pdo->prepare("SELECT u_id, username, email, role_id, xp FROM users WHERE u_id = ?");
    $fetch->execute([$newId]);
    $created = $fetch->fetch(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    // ignore fetch error; still return success
  }

  echo json_encode([
    "success" => true,
    "message" => "User created",
    "id" => $newId,
    "user" => $created,
  ]);
  exit;
}

/* ---------------- UPDATE USER ------------------- */
if ($action === "update_user") {
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Bad CSRF"]);
    exit;
  }
  require_role("admin");
  $id       = $input["id"] ?? ($input["u_id"] ?? ($_GET["id"] ?? null));
  $username = $input["username"] ?? ($_GET["username"] ?? null);
  $email    = isset($input["email"]) ? trim($input["email"]) : (isset($_GET["email"]) ? trim($_GET["email"]) : null);
  $password = $input["password"] ?? ($_GET["password"] ?? null);
  $role     = isset($input["role"]) ? intval($input["role"]) : (isset($_GET["role"]) ? intval($_GET["role"]) : null);

  if (!$id) {
    echo json_encode(["success" => false, "message" => "Missing id"]);
    exit;
  }

  // normalize role
  if ($role !== null && !in_array($role, [1, 2, 3])) {
    $role = 1;
  }

  $fields = [];
  $values = [];

  if ($username !== null && $username !== "") {
    $fields[] = "username = ?";
    $values[] = $username;
  }
  if ($email !== null) {
    $fields[] = "email = ?";
    $values[] = $email === "" ? null : $email;
  }
  if ($role !== null) {
    $fields[] = "role_id = ?";
    $values[] = $role;
  }
  if ($password !== null && $password !== "") {
    $fields[] = "password = ?";
    $values[] = password_hash($password, PASSWORD_DEFAULT);
  }

  if (!$fields) {
    echo json_encode(["success" => false, "message" => "Nothing to update"]);
    exit;
  }

  $values[] = $id;
  $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE u_id = ?";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($values);

  echo json_encode(["success" => true]);
  exit;
}

/* ---------------- DELETE USER ------------------- */
if ($action === "delete_user") {
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Bad CSRF"]);
    exit;
  }
  require_role("admin");
  $id = $input["id"] ?? ($input["u_id"] ?? ($_GET["id"] ?? null));
  if (!$id) {
    echo json_encode(["success" => false, "message" => "Missing id"]);
    exit;
  }

  try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE u_id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "deleted" => true]);
  } catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
  }
  exit;
}


/* ---------------- LIST USERS (FOR ADMIN DASHBOARD) ------------------- */
/* supports both:
   - POST { action: "users" }
   - GET  ?action=users
*/
if ($action === "users" || $action === "list_users") {
  require_logged_in(["admin"]);
  try {
    $stmt = $pdo->query("
      SELECT u.u_id, u.username, u.email, u.xp, u.role_id, r.role_name
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
