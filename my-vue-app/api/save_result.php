<?php
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (
  !isset($data["user_id"]) ||
  !isset($data["exercise_id"]) ||
  !isset($data["score"]) ||
  !isset($data["total"])
) {
  echo json_encode(["error" => "Missing values"]);
  exit;
}

$userId = (int)$data["user_id"];
$exerciseId = (int)$data["exercise_id"];
$score = isset($data["score"]) ? (int)$data["score"] : 0;
$total = isset($data["total"]) ? (int)$data["total"] : 0;

if ($total > 0 && $score <= $total) {
  $percent = ($score / $total) * 100;
} else {
  $percent = $score;
}

$percentRounded = round($percent);
$passed = $percentRounded >= 70 ? 1 : 0;

try {
  $stmt = $pdo->prepare("
    INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed, Completed_At)
    VALUES (?, ?, ?, ?, NOW())
  ");
  $stmt->execute([$userId, $exerciseId, $percentRounded, $passed]);

  if ($passed) {
    $xp = 50;

    $update = $pdo->prepare("
      UPDATE users
      SET xp = xp + ?
      WHERE u_id = ?
    ");
    $update->execute([$xp, $userId]);
  }

  $xpGet = $pdo->prepare("SELECT xp FROM users WHERE u_id = ?");
  $xpGet->execute([$userId]);
  $currentXp = $xpGet->fetchColumn();

  echo json_encode([
    "success" => true,
    "passed" => $passed,
    "percent" => $percentRounded,
    "new_xp" => $currentXp
  ]);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>
