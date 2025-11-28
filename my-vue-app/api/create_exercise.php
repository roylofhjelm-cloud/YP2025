<?php
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

// Create debug file to inspect payload
file_put_contents("debug_input.txt", json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if (!$input || !isset($input["Title"]) || !isset($input["Type"])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$title = $input["Title"];
$description = $input["Description"] ?? "";
$type = $input["Type"]; // "mixed"
$created_by = $input["Created_By"] ?? 1;

// questions are inside Data.questions
$questions = isset($input["Data"]["questions"]) && is_array($input["Data"]["questions"])
    ? $input["Data"]["questions"]
    : [];

try {
    // 1️⃣ Insert exercise
    $stmt = $pdo->prepare("
        INSERT INTO exercises (Title, Description, Type, Created_By)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$title, $description, $type, $created_by]);
    $exerciseId = $pdo->lastInsertId();

    // 2️⃣ Insert each question
    if (!empty($questions)) {

        $qStmt = $pdo->prepare("
            INSERT INTO exercise_questions (Exercise_Id, Statement, Question_Type, Data)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($questions as $q) {

            // New format:  { type: "...", data: {...} }
            $qType = $q["type"] ?? "mcq";
            $qData = $q["data"] ?? [];

            // Always extract a visible statement
            $statement = $qData["text"] ?? "Fråga";

            // Save JSON (type + data)
            $json = json_encode([
                "type" => $qType,
                "data" => $qData
            ], JSON_UNESCAPED_UNICODE);

            $qStmt->execute([$exerciseId, $statement, $qType, $json]);
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
