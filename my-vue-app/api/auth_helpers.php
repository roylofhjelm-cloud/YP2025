<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
