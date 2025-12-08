<?php
// Save result API: persists quiz score and updates XP with CSRF + session checks.
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true) ?? [];

if (!verify_csrf($data)) {
  http_response_code(400);
  echo json_encode(["error" => "Bad CSRF"]);
  exit;
}

require_logged_in(["student", "admin"]);

$sessionUser = $_SESSION["user_id"] ?? 0;
$userId = isset($data["user_id"]) ? (int)$data["user_id"] : 0;
$exerciseId = isset($data["exercise_id"]) ? (int)$data["exercise_id"] : 0;
$score = isset($data["score"]) ? (float)$data["score"] : 0;
$total = isset($data["total"]) ? (float)$data["total"] : 100;

if ($userId <= 0 || $exerciseId <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "Missing values"]);
  exit;
}

// student can only save their own result
if ($_SESSION["role"] === "student" && $sessionUser !== $userId) {
  http_response_code(403);
  echo json_encode(["error" => "Forbidden"]);
  exit;
}

$percent = $total > 0 ? ($score / $total) * 100 : $score;
$percentRounded = max(0, min(100, round($percent)));
$passed = $percentRounded >= 70 ? 1 : 0;

try {
  $stmt = $pdo->prepare("
    INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed, Completed_At)
    VALUES (?, ?, ?, ?, NOW())
  ");
  $stmt->execute([$userId, $exerciseId, $percentRounded, $passed]);

  $xpAward = 0;
  if ($passed) {
    $xpAward = max(20, min(80, ($percentRounded - 60) * 2));
    $update = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id = ?");
    $update->execute([$xpAward, $userId]);
  }

  $xpGet = $pdo->prepare("SELECT xp FROM users WHERE u_id = ?");
  $xpGet->execute([$userId]);
  $currentXp = $xpGet->fetchColumn();

  echo json_encode([
    "success" => true,
    "passed" => $passed,
    "percent" => $percentRounded,
    "xp_gained" => $xpAward,
    "new_xp" => (int)$currentXp,
  ]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>
