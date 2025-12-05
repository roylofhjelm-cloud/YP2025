<?php
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["user_id"], $input["exercise_id"], $input["score"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$user_id = intval($input["user_id"]);
$exercise_id = intval($input["exercise_id"]);
$score = intval($input["score"]);

// XP Calculation: only if pass >= 70
$xp_gained = 0;
if ($score >= 70) {
    $xp_gained = ($score - 60) * 2;
    if ($xp_gained < 20) $xp_gained = 20;
    if ($xp_gained > 80) $xp_gained = 80;
}

try {
    // Save result
    $stmt = $pdo->prepare("
        INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$user_id, $exercise_id, $score]);

    // Give XP
    if ($xp_gained > 0) {
        $stmt = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id = ?");
        $stmt->execute([$xp_gained, $user_id]);
    }

    echo json_encode([
        "success" => true,
        "score" => $score,
        "xp_gained" => $xp_gained
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}