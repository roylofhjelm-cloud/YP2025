<?php
require_once "config.php";

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "http://localhost",
  "http://127.0.0.1",
];
if (in_array($origin, $allowedOrigins, true)) {
  header("Access-Control-Allow-Origin: $origin");
} else {
  header("Access-Control-Allow-Origin: *");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit;
}

/* GET all materials */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $stmt = $pdo->query("SELECT * FROM materials ORDER BY Created_At DESC");
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
  exit;
}

/* CREATE material */
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['Title']) || !isset($input['Content'])) {
  echo json_encode(["error" => "Missing fields"]);
  exit;
}

$stmt = $pdo->prepare("
  INSERT INTO materials (Title, Content, Created_By)
  VALUES (?, ?, ?)
");

$stmt->execute([
  $input['Title'],
  $input['Content'],
  $input['Created_By'] ?? null
]);

echo json_encode(["success" => true]);
