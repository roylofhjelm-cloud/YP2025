<?php
require_once "config.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$user_id = isset($_GET["user_id"]) ? intval($_GET["user_id"]) : 0;
if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Missing user_id"]);
    exit();
}

try {
    // Fetch user row
    $stmt = $pdo->prepare("SELECT u_id, username, xp FROM users WHERE u_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["error" => "User not found"]);
        exit();
    }

    // Determine level based on XP_Required thresholds
    $stmt = $pdo->prepare("
        SELECT Level_Id, Level_Name, XP_Required
        FROM experience_levels
        WHERE XP_Required <= ?
        ORDER BY XP_Required DESC
        LIMIT 1
    ");
    $stmt->execute([$user["xp"]]);
    $level = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fallback level if none found
    if (!$level) {
        $level = ["Level_Id" => 1, "Level_Name" => "1", "XP_Required" => 0];
    }

    // Next level threshold
    $stmt = $pdo->prepare("
        SELECT XP_Required
        FROM experience_levels
        WHERE XP_Required > ?
        ORDER BY XP_Required ASC
        LIMIT 1
    ");
    $stmt->execute([$user["xp"]]);
    $nextLevel = $stmt->fetch(PDO::FETCH_ASSOC);
    $next_xp = $nextLevel ? intval($nextLevel["XP_Required"]) : null;

    // User stats
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS completed, AVG(Score) AS avg_score
        FROM user_results
        WHERE User_Id = ?
    ");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    $completed = $stats && $stats["completed"] !== null ? intval($stats["completed"]) : 0;
    $avg_score = $stats && $stats["avg_score"] !== null ? round(floatval($stats["avg_score"]), 1) : 0.0;

    echo json_encode([
        "user" => [
            "id" => intval($user["u_id"]),
            "username" => $user["username"],
            "xp" => intval($user["xp"]),
        ],
        "level" => [
            "level_id" => intval($level["Level_Id"]),
            "level_name" => strval($level["Level_Id"]), // show as number
            "xp_required" => intval($level["XP_Required"]),
        ],
        "next_level_xp" => $next_xp,
        "stats" => [
            "completed_exercises" => $completed,
            "average_score" => $avg_score,
        ],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
