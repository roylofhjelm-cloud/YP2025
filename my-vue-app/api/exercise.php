<?php
require_once "config.php";
header("Content-Type: application/json; charset=UTF-8");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(["error" => "Invalid id"]);
    exit;
}

try {

    // 1️⃣ Load exercise
    $stmt = $pdo->prepare("SELECT * FROM exercises WHERE Exercise_Id = ?");
    $stmt->execute([$id]);
    $exercise = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exercise) {
        echo json_encode(["error" => "Exercise not found"]);
        exit;
    }

    // 2️⃣ Load questions (JSON format)
    $q = $pdo->prepare("SELECT * FROM exercise_questions WHERE Exercise_Id = ?");
    $q->execute([$id]);
    $rows = $q->fetchAll(PDO::FETCH_ASSOC);

    $questions = [];

    foreach ($rows as $row) {
        $data = json_decode($row["Data"], true);

        $questions[] = [
            "Question_Id" => $row["Question_Id"],
            "Question_Type" => $row["Question_Type"],
            "Data" => $data   // contain text + options etc
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
