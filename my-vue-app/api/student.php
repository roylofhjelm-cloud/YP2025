<?php
require_once "config.php";
require_once "auth_helpers.php";

// Allow local dev and live domain
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
];
if (in_array($origin, $allowedOrigins, true)) {
  header("Access-Control-Allow-Origin: $origin");
} else {
  header("Access-Control-Allow-Origin: https://yp2025.rf.gd");
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if(!$input || !isset($input["action"])){
  echo json_encode(["success"=>false]);
  exit;
}

/* ------------ LOGIN ------------ */
if($input["action"] === "login"){

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role_id = 1");
  $stmt->execute([$input["username"]]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  $validPassword = false;
  if ($user && strlen($input["password"] ?? "") >= 3) {
    $validPassword =
      $input["password"] === ($user["password"] ?? "") ||
      password_verify($input["password"], $user["password"] ?? "");
  }

  if($user && $validPassword){
    session_regenerate_id(true);
    $_SESSION["user_id"] = $user["u_id"];
    $_SESSION["role"] = "student";
    $csrf = issue_csrf();
    echo json_encode([
      "success"=>true,
      "user" => [
        "u_id" => $user["u_id"],
        "username" => $user["username"],
        "xp" => $user["xp"]
      ],
      "csrf_token" => $csrf
    ]);
  } else {
    echo json_encode(["success"=>false]);
  }

  exit;
}


/* ------------ SAVE RESULT ------------ */
if($input["action"] === "save_result"){

  $userId = $input["user_id"];
  $exerciseId = $input["exercise_id"];
  $score = $input["score"];

  $stmt = $pdo->prepare("
    INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed)
    VALUES (?, ?, ?, 1)
  ");

  $stmt->execute([$userId, $exerciseId, $score]);

  // Give XP (10 per correct)
  $earnedXP = $score * 10;

  $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id = ?")
      ->execute([$earnedXP,$userId]);

  echo json_encode(["success"=>true, "xp_awarded"=>$earnedXP]);
  exit;
}
