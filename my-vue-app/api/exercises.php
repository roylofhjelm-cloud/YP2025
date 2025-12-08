<?php
// Exercises API: list all, create/update, or delete exercises (admin CRUD + student listing).
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

$method = $_SERVER['REQUEST_METHOD'];

function clean_text($value) {
  $trimmed = trim((string)$value);
  return strip_tags($trimmed);
}

try {
  if ($method === 'GET') {
      require_logged_in(["student", "admin"]);
      $stmt = $pdo->query("SELECT * FROM exercises ORDER BY Exercise_Id DESC");
      $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode(["success" => true, "exercises" => $exercises]);
      exit;
  }

  if ($method === 'POST') {
      $input = json_decode(file_get_contents("php://input"), true) ?? [];
      if (!verify_csrf($input)) {
          http_response_code(400);
          echo json_encode(["success" => false, "error" => "Bad CSRF"]);
          exit;
      }
      require_role("admin");

      // allow explicit delete via action for hosts that block DELETE
      if (($input["action"] ?? "") === "delete") {
          $id = isset($input["Exercise_Id"]) ? intval($input["Exercise_Id"]) : 0;
          if ($id <= 0) {
              echo json_encode(["success" => false, "error" => "Missing Exercise_Id"]);
              exit;
          }
          $stmt = $pdo->prepare("DELETE FROM exercises WHERE Exercise_Id = ?");
          $stmt->execute([$id]);
          echo json_encode(["success" => true, "deleted" => true]);
          exit;
      }

      $id = isset($input["Exercise_Id"]) ? intval($input["Exercise_Id"]) : 0;
      $title = isset($input["Title"]) ? clean_text($input["Title"]) : null;
      $description = isset($input["Description"]) ? clean_text($input["Description"]) : null;
      $type = isset($input["Type"]) ? clean_text($input["Type"]) : null;
      $questions = isset($input["Data"]["questions"]) && is_array($input["Data"]["questions"])
          ? $input["Data"]["questions"]
          : [];

      if ($id <= 0) {
          echo json_encode(["success" => false, "error" => "Missing Exercise_Id"]);
          exit;
      }

      $fields = [];
      $values = [];
      if ($title !== null && $title !== "") { $fields[] = "Title = ?"; $values[] = mb_substr($title, 0, 255); }
      if ($description !== null) { $fields[] = "Description = ?"; $values[] = mb_substr($description, 0, 1000); }
      if ($type !== null && $type !== "") { $fields[] = "Type = ?"; $values[] = $type; }

      if ($fields) {
          $values[] = $id;
          $sql = "UPDATE exercises SET " . implode(", ", $fields) . " WHERE Exercise_Id = ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute($values);
      }

      if (!empty($questions)) {
          $pdo->prepare("DELETE FROM exercise_questions WHERE Exercise_Id = ?")->execute([$id]);

          $qStmt = $pdo->prepare("
              INSERT INTO exercise_questions (Exercise_Id, Statement, Question_Type, Data)
              VALUES (?, ?, ?, ?)
          ");

          foreach ($questions as $q) {
              $qType = clean_text($q["type"] ?? ($q["Question_Type"] ?? "mcq"));
              $qData = $q["data"] ?? ($q["Data"] ?? []);
              $statementRaw = $qData["text"] ?? ($q["Statement"] ?? "Fråga");
              $statement = mb_substr(clean_text($statementRaw), 0, 500);

              $json = json_encode([
                  "type" => $qType,
                  "data" => $qData
              ], JSON_UNESCAPED_UNICODE);

              $qStmt->execute([$id, $statement, $qType, $json]);
          }
      }

      echo json_encode(["success" => true]);
      exit;
  }

  if ($method === 'DELETE') {
      $raw = file_get_contents("php://input");
      $input = json_decode($raw, true) ?? [];
      if (!verify_csrf($input)) {
          http_response_code(400);
          echo json_encode(["success" => false, "error" => "Bad CSRF"]);
          exit;
      }
      require_role("admin");
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
