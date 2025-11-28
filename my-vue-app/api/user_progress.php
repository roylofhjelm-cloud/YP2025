<?php
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$userId = isset($_GET["user_id"]) ? (int) $_GET["user_id"] : 0;

if ($userId === 0) {
    echo json_encode(["error" => "Missing user_id"]);
    exit;
}

try {
    // Aggregate stats using the latest definition
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            ROUND(AVG(CASE WHEN Score > 0 THEN Score END)) as average_score,
            SUM(Score > 0) as attempts
        FROM user_results
        WHERE User_Id = ?
    ");
    $stmt->execute([$userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total" => 0, "average_score" => 0, "attempts" => 0];

    // Recent exercise results
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

    // Current level derived from XP
    $stmt3 = $pdo->prepare("SELECT xp FROM users WHERE u_id = ?");
    $stmt3->execute([$userId]);
    $user = $stmt3->fetch(PDO::FETCH_ASSOC);
    $xp = isset($user["xp"]) ? (int) $user["xp"] : 0;

    $stmt4 = $pdo->prepare("
        SELECT Level_Name
        FROM experience_levels
        WHERE XP_Required <= ?
        ORDER BY XP_Required DESC
        LIMIT 1
    ");
    $stmt4->execute([$xp]);
    $level = $stmt4->fetch(PDO::FETCH_ASSOC);

    file_put_contents("debug_avg.txt", json_encode($stats, JSON_PRETTY_PRINT));

    echo json_encode([
        "stats" => $stats,
        "results" => $results,
        "level" => $level["Level_Name"] ?? "Beginner",
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
