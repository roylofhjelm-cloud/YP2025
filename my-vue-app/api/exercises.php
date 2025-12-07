<?php
require_once "config.php";
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Fetch all exercises from DB
        $stmt = $pdo->query("SELECT * FROM exercises ORDER BY Exercise_Id DESC");
        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "exercises" => $exercises]);
        exit;
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true) ?? [];
        $id = isset($input["Exercise_Id"]) ? intval($input["Exercise_Id"]) : 0;
        $title = $input["Title"] ?? null;
        $description = $input["Description"] ?? null;
        $type = $input["Type"] ?? null;

        if ($id <= 0) {
            echo json_encode(["success" => false, "error" => "Missing Exercise_Id"]);
            exit;
        }

        $fields = [];
        $values = [];
        if ($title !== null) { $fields[] = "Title = ?"; $values[] = $title; }
        if ($description !== null) { $fields[] = "Description = ?"; $values[] = $description; }
        if ($type !== null) { $fields[] = "Type = ?"; $values[] = $type; }

        if (!$fields) {
            echo json_encode(["success" => false, "error" => "Nothing to update"]);
            exit;
        }

        $values[] = $id;
        $sql = "UPDATE exercises SET " . implode(", ", $fields) . " WHERE Exercise_Id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);

        echo json_encode(["success" => true]);
        exit;
    }

    if ($method === 'DELETE') {
        $raw = file_get_contents("php://input");
        $input = json_decode($raw, true);
        $id = $input["Exercise_Id"] ?? ($input["id"] ?? ($_GET["id"] ?? null));
        $id = intval($id);
        if ($id <= 0) {
            echo json_encode(["success" => false, "error" => "Missing Exercise_Id"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM exercises WHERE Exercise_Id = ?");
        $stmt->execute([$id]);
        echo json_encode(["success" => true, "deleted" => true]);
        exit;
    }

    http_response_code(405);
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to process request: " . $e->getMessage()]);
}
?>
