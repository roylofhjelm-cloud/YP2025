<?php
// User stats API: returns XP, level (with fallback to formula), and summary stats for a user.
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
    "http://localhost:8080",
    "http://127.0.0.1:8080",
    "https://yp2025.rf.gd",
    "http://yp2025.rf.gd",
]);
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");

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

require_logged_in(["student", "admin"]);
if ($_SESSION["role"] === "student" && ($user_id !== ($_SESSION["user_id"] ?? 0))) {
    http_response_code(403);
    echo json_encode(["error" => "Forbidden"]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT u_id, username, xp FROM users WHERE u_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["error" => "User not found"]);
        exit();
    }

    // Figure out current level (highest XP_Required <= user xp)
    $stmt = $pdo->prepare("
        SELECT Level_Id, Level_Name, XP_Required
        FROM experience_levels
        WHERE XP_Required <= ?
        ORDER BY XP_Required DESC
        LIMIT 1
    ");
    $stmt->execute([$user["xp"]]);
    $level = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$level) {
        $level = ["Level_Id" => 1, "Level_Name" => "1", "XP_Required" => 0];
    }

    // Next level from table if available
    $stmt = $pdo->prepare("
        SELECT Level_Id, XP_Required
        FROM experience_levels
        WHERE XP_Required > ?
        ORDER BY XP_Required ASC
        LIMIT 1
    ");
    $stmt->execute([$user["xp"]]);
    $nextLevel = $stmt->fetch(PDO::FETCH_ASSOC);

    // If table runs out, extend virtually with a formula (triangular progression * 100)
    $next_xp = null;
    $next_level_id = null;
    if ($nextLevel) {
        $next_xp = intval($nextLevel["XP_Required"]);
        $next_level_id = intval($nextLevel["Level_Id"]);
    } else {
        $currentId = intval($level["Level_Id"]);
        $next_level_id = $currentId + 1;
        $next_xp = intval((($next_level_id * ($next_level_id - 1)) / 2) * 100);
    }

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
            "level_name" => strval($level["Level_Id"]),
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
