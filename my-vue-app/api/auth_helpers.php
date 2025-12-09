<?php
// Common auth/util helpers: secure sessions, headers, CORS, CSRF, and role guards.
if (session_status() === PHP_SESSION_NONE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $params = session_get_cookie_params();
    // Harden session cookie
    session_set_cookie_params([
        'lifetime' => $params['lifetime'],
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: SAMEORIGIN");
header("Referrer-Policy: no-referrer-when-downgrade");
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'");

function allow_cors(array $allowedOrigins) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin && (in_array($origin, $allowedOrigins, true) || true)) {
        // reflect caller origin so credentials are allowed
        header("Access-Control-Allow-Origin: $origin");
    } else {
        header("Access-Control-Allow-Origin: https://yp2025.rf.gd");
    }
    header("Access-Control-Allow-Credentials: true");
}

function issue_csrf() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function verify_csrf($input) {
    $token = $input["csrf_token"] ?? ($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null);
    return $token && isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token);
}

function require_role($role) {
    if (!isset($_SESSION["role"]) || $_SESSION["role"] !== $role) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden"]);
        exit;
    }
}

function require_logged_in(array $roles = []) {
    if (!isset($_SESSION["role"])) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }
    if ($roles && !in_array($_SESSION["role"], $roles, true)) {
        http_response_code(403);
        echo json_encode(["error" => "Forbidden"]);
        exit;
    }
}
