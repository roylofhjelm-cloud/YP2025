<?php
// User progress API: aggregates results for dashboard (totals, average score, recent results).
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
    exit;
}

$userId = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : 0;
if ($userId === 0) {
    echo json_encode(["error" => "Missing user_id"]);
    exit;
}

require_logged_in(["student", "admin"]);
if ($_SESSION["role"] === "student" && ($userId !== ($_SESSION["user_id"] ?? 0))) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN Completed = 1 THEN 1 ELSE 0 END) as total_completed,
            ROUND(AVG(Score)) as average_score
        FROM user_results
        WHERE User_Id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total_completed" => 0, "average_score" => 0];

    $stmt2 = $pdo->prepare("
        SELECT 
            ur.Exercise_Id,
            ur.Result_Id,
            e.Title,
            ur.Score,
            ur.Completed,
            ur.Completed_At
        FROM user_results ur
        JOIN exercises e ON ur.Exercise_Id = e.Exercise_Id
        WHERE ur.User_Id = ?
        ORDER BY ur.Completed_At DESC, ur.Result_Id DESC
    ");
    $stmt2->execute([$userId]);
    $resultsRaw = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $results = array_map(function ($row) {
        $score = isset($row["Score"]) ? (float) $row["Score"] : 0;
        $row["Percent"] = max(0, min(100, round($score)));
        return $row;
    }, $resultsRaw);

    $stmt3 = $pdo->prepare("SELECT xp FROM users WHERE u_id = ?");
    $stmt3->execute([$userId]);
    $user = $stmt3->fetch(PDO::FETCH_ASSOC);
    $xp = isset($user["xp"]) ? (int) $user["xp"] : 0;

    $stmt4 = $pdo->prepare("
        SELECT Level_Id, Level_Name, XP_Required
        FROM experience_levels
        WHERE XP_Required <= ?
        ORDER BY XP_Required DESC
        LIMIT 1
    ");
    $stmt4->execute([$xp]);
    $level = $stmt4->fetch(PDO::FETCH_ASSOC);

    $stmt5 = $pdo->prepare("
        SELECT XP_Required
        FROM experience_levels
        WHERE XP_Required > ?
        ORDER BY XP_Required ASC
        LIMIT 1
    ");
    $stmt5->execute([$xp]);
    $nextLevel = $stmt5->fetch(PDO::FETCH_ASSOC);
    $next_xp = $nextLevel ? (int)$nextLevel["XP_Required"] : null;

    echo json_encode([
        "stats" => [
            "total" => (int)$stats["total_completed"],
            "average_score" => (int)$stats["average_score"]
        ],
        "results" => $results,
        "level" => [
            "id" => isset($level["Level_Id"]) ? (int)$level["Level_Id"] : 1,
            "name" => $level["Level_Name"] ?? "1",
            "xp_required" => isset($level["XP_Required"]) ? (int)$level["XP_Required"] : 0,
        ],
        "xp" => $xp,
        "next_level_xp" => $next_xp,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
