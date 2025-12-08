<?php
// Materials API: list materials for logged-in users; admin can create/update/delete with CSRF.
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

/* GET all materials (require login) */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  require_logged_in(["student", "admin"]);
  $stmt = $pdo->query("SELECT * FROM materials ORDER BY Created_At DESC");
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/* CREATE / UPDATE / DELETE (admin only + CSRF) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($input["action"] ?? "") === "delete") {
    if (!verify_csrf($input)) {
      http_response_code(400);
      echo json_encode(["error" => "Bad CSRF"]);
      exit;
    }
    require_role("admin");
    $id = $input["Material_Id"] ?? ($input["id"] ?? null);
    if (!$id) {
      echo json_encode(["error" => "Missing id"]);
      exit;
    }
    $stmt = $pdo->prepare("DELETE FROM materials WHERE Material_Id = ?");
    $stmt->execute([$id]);
    echo json_encode(["success" => true, "deleted" => true]);
    exit;
  }

  require_role("admin");
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Bad CSRF"]);
    exit;
  }

  $title = strip_tags(trim($input['Title'] ?? ""));
  $content = strip_tags(trim($input['Content'] ?? ""), "<br><p><strong><em><ul><ol><li>");

  if (strlen($title) < 1 || strlen($content) < 1) {
    echo json_encode(["error" => "Missing fields"]);
    exit;
  }

  $stmt = $pdo->prepare("
    INSERT INTO materials (Title, Content, Created_By)
    VALUES (?, ?, ?)
  ");

  $stmt->execute([
    $title,
    $content,
    $_SESSION["user_id"] ?? null
  ]);

  echo json_encode(["success" => true]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  require_role("admin");
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Bad CSRF"]);
    exit;
  }

  $id = $input["Material_Id"] ?? ($input["id"] ?? null);
  $title = strip_tags(trim($input["Title"] ?? ""));
  $content = strip_tags(trim($input["Content"] ?? ""), "<br><p><strong><em><ul><ol><li>");
  if (!$id || strlen($title) < 1 || strlen($content) < 1) {
    echo json_encode(["error" => "Missing fields"]);
    exit;
  }

  $stmt = $pdo->prepare("UPDATE materials SET Title = ?, Content = ? WHERE Material_Id = ?");
  $stmt->execute([$title, $content, $id]);
  echo json_encode(["success" => true]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  require_role("admin");
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Bad CSRF"]);
    exit;
  }

  $id = $input["Material_Id"] ?? ($input["id"] ?? null);
  if (!$id) {
    echo json_encode(["error" => "Missing id"]);
    exit;
  }

  $stmt = $pdo->prepare("DELETE FROM materials WHERE Material_Id = ?");
  $stmt->execute([$id]);
  echo json_encode(["success" => true, "deleted" => true]);
  exit;
}
