<?php
// config.php - database connection and permissive CORS for local/prod

// CORS: reflect origin when present so credentials pass
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: https://yp2025.rf.gd");
}
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Credentials: true");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$host = "localhost";
$dbname = "learningportal";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed", "details" => $e->getMessage()]);
    exit;
}
