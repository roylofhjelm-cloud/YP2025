<?php
require_once "config.php";
header('Content-Type: application/json; charset=utf-8');

try {
    // Fetch all exercises from DB
    $stmt = $pdo->query("SELECT * FROM exercises ORDER BY Exercise_Id ASC");
    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($exercises);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to fetch exercises: " . $e->getMessage()]);
}
?>
