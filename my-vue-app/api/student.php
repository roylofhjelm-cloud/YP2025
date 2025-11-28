<?php
require_once "config.php";
header("Access-Control-Allow-Origin: http://localhost:8080");
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

  if($user && $input["password"] === $user["password"]){
    echo json_encode([
      "success"=>true,
      "user" => [
        "u_id" => $user["u_id"],
        "username" => $user["username"],
        "xp" => $user["xp"]
      ]
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
