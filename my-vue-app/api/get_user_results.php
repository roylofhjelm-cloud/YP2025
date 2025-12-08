<?php
// User results API: fetches quiz results for a given user with access control.
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$userId = isset($_GET["user_id"]) ? intval($_GET["user_id"]) : null;

if (!$userId) {
  echo json_encode([]);
  exit;
}

require_logged_in(["student", "admin"]);
if ($_SESSION["role"] === "student" && $userId !== ($_SESSION["user_id"] ?? 0)) {
  http_response_code(403);
  echo json_encode(["error" => "Forbidden"]);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT 
      ur.Result_Id,
      ur.Score,
      ur.Completed,
      ur.Completed_At,
      e.Title,
      e.Type
    FROM user_results ur
    JOIN exercises e ON ur.Exercise_Id = e.Exercise_Id
    WHERE ur.User_Id = ?
    ORDER BY ur.Completed_At DESC
  ");

  $stmt->execute([$userId]);

  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>
