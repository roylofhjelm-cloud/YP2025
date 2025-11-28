<?php
require_once "config.php";

header("Access-Control-Allow-Origin: http://localhost:8080");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

$userId = isset($_GET["user_id"]) ? intval($_GET["user_id"]) : null;

if (!$userId) {
  echo json_encode([]);
  exit;
}

try {
  $stmt = $pdo->prepare("
    SELECT 
      ur.Result_Id,
      ur.Score,
      ur.Completed,
      ur.Completed_At,
      e.Title,
      e.Type
    FROM user_results ur
    JOIN exercises e ON ur.Exercise_Id = e.Exercise_Id
    WHERE ur.User_Id = ?
    ORDER BY ur.Completed_At DESC
  ");

  $stmt->execute([$userId]);

  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>
