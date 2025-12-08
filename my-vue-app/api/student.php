<?php
// Student API: handles student login and saving quiz results with XP updates.
require_once "config.php";
require_once "auth_helpers.php";

allow_cors([
  "http://localhost:8080",
  "http://127.0.0.1:8080",
  "https://yp2025.rf.gd",
  "http://yp2025.rf.gd",
]);
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");
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

  if (strlen($input["username"] ?? "") < 3 || strlen($input["password"] ?? "") < 3) {
    echo json_encode(["success"=>false, "message"=>"Ogiltiga uppgifter"]);
    exit;
  }

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
  if (!verify_csrf($input)) {
    http_response_code(400);
    echo json_encode(["success"=>false, "error"=>"Bad CSRF"]);
    exit;
  }
  require_logged_in(["student"]);

  $userId = (int)$input["user_id"];
  $exerciseId = (int)$input["exercise_id"];
  $score = (float)$input["score"];

  if ($userId <= 0 || $exerciseId <= 0) {
    echo json_encode(["success"=>false, "error"=>"Missing fields"]);
    exit;
  }
  if ($userId !== ($_SESSION["user_id"] ?? 0)) {
    http_response_code(403);
    echo json_encode(["success"=>false, "error"=>"Forbidden"]);
    exit;
  }

  $stmt = $pdo->prepare("
    INSERT INTO user_results (User_Id, Exercise_Id, Score, Completed)
    VALUES (?, ?, ?, 1)
  ");

  $stmt->execute([$userId, $exerciseId, $score]);

  $earnedXP = max(0, min(100, round($score))) >= 70 ? max(20, min(80, ($score - 60) * 2)) : 0;

  if ($earnedXP > 0) {
    $pdo->prepare("UPDATE users SET xp = xp + ? WHERE u_id = ?")
        ->execute([$earnedXP,$userId]);
  }

  echo json_encode(["success"=>true, "xp_awarded"=>$earnedXP]);
  exit;
}
