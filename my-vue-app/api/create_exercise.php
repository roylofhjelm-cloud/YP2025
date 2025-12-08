<?php
// Create exercise endpoint: admin-only, accepts mixed question payload and stores exercise + questions.
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

$input = json_decode(file_get_contents("php://input"), true) ?? [];

if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Bad CSRF"]);
    exit;
}
require_role("admin");

$title = trim($input["Title"] ?? "");
$description = trim($input["Description"] ?? "");
$type = trim($input["Type"] ?? "mixed"); // default mixed
$created_by = $_SESSION["user_id"] ?? null;

$questions = isset($input["Data"]["questions"]) && is_array($input["Data"]["questions"])
    ? $input["Data"]["questions"]
    : [];

if (strlen($title) < 1 || strlen($type) < 2) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO exercises (Title, Description, Type, Created_By)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        strip_tags(mb_substr($title, 0, 255)),
        strip_tags(mb_substr($description, 0, 1000)),
        strip_tags($type),
        $created_by
    ]);
    $exerciseId = $pdo->lastInsertId();

    if (!empty($questions)) {
        $qStmt = $pdo->prepare("
            INSERT INTO exercise_questions (Exercise_Id, Statement, Question_Type, Data)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($questions as $q) {
            $qType = strip_tags($q["type"] ?? "mcq");
            $qData = $q["data"] ?? [];
            $statement = $qData["text"] ?? "Fråga";

            $json = json_encode([
                "type" => $qType,
                "data" => $qData
            ], JSON_UNESCAPED_UNICODE);

            $qStmt->execute([$exerciseId, strip_tags(mb_substr($statement, 0, 500)), $qType, $json]);
        }
    }

    echo json_encode([
        "success" => true,
        "Exercise_Id" => $exerciseId,
        "Inserted_Questions" => count($questions)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
