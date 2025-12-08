<?php
// Single exercise fetch: returns exercise metadata and questions for logged-in users.
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
    exit;
}

require_logged_in(["student", "admin"]);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(["error" => "Invalid id"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Exercise_Id = ?");
    $stmt->execute([$id]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exercise) {
        echo json_encode(["error" => "Exercise not found"]);
        exit;
    }

    $q = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id = ?");
    $q->execute([$id]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];
    foreach ($rows as $row) {
        $data = json_decode($row["Data"], true);

        $questions[] = [
            "Question_Id" => $row["Question_Id"],
            "Question_Type" => $row["Question_Type"],
            "Data" => $data
        ];
    }

    echo json_encode([
        "exercise" => $exercise,
        "questions" => $questions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
