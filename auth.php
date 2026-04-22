<?php
function is_https_request()
{
    return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");
}

function start_app_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Vercel allows writing only to /tmp during a request.
    if (getenv("VERCEL")) {
        $session_path = sys_get_temp_dir() . "/todo-list-sessions";

        if (!is_dir($session_path)) {
            @mkdir($session_path, 0777, true);
        }

        if (is_dir($session_path)) {
            session_save_path($session_path);
        }
    }

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => is_https_request(),
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

function get_auth_cookie_name()
{
    return "todo_auth";
}

function get_auth_cookie_secret()
{
    $secret = getenv("AUTH_COOKIE_SECRET");

    if ($secret !== false && $secret !== "") {
        return $secret;
    }

    $fallback = getenv("DB_PASSWORD") ?: "local-dev-auth-secret";
    return hash("sha256", "todo-auth|" . $fallback);
}

function base64url_encode($value)
{
    return rtrim(strtr(base64_encode($value), "+/", "-_"), "=");
}

function base64url_decode($value)
{
    $padding = strlen($value) % 4;

    if ($padding > 0) {
        $value .= str_repeat("=", 4 - $padding);
    }

    return base64_decode(strtr($value, "-_", "+/"), true);
}

function set_auth_cookie($user_id, $user_name)
{
    $payload = json_encode([
        "user_id" => (int) $user_id,
        "user_name" => (string) $user_name
    ]);

    if ($payload === false) {
        return;
    }

    $encoded_payload = base64url_encode($payload);
    $signature = hash_hmac("sha256", $encoded_payload, get_auth_cookie_secret());
    $token = $encoded_payload . "." . $signature;

    setcookie(get_auth_cookie_name(), $token, [
        "expires" => 0,
        "path" => "/",
        "secure" => is_https_request(),
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

function clear_auth_cookie()
{
    unset($_COOKIE[get_auth_cookie_name()]);

    setcookie(get_auth_cookie_name(), "", [
        "expires" => time() - 3600,
        "path" => "/",
        "secure" => is_https_request(),
        "httponly" => true,
        "samesite" => "Lax"
    ]);
}

function clear_auth_state()
{
    clear_auth_cookie();

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }

    start_app_session();
}

function restore_auth_from_cookie()
{
    if (isset($_SESSION["user_id"])) {
        return;
    }

    $token = $_COOKIE[get_auth_cookie_name()] ?? "";

    if ($token === "" || !str_contains($token, ".")) {
        return;
    }

    [$encoded_payload, $signature] = explode(".", $token, 2);
    $expected_signature = hash_hmac("sha256", $encoded_payload, get_auth_cookie_secret());

    if (!hash_equals($expected_signature, $signature)) {
        clear_auth_cookie();
        return;
    }

    $payload = base64url_decode($encoded_payload);

    if ($payload === false) {
        clear_auth_cookie();
        return;
    }

    $auth_data = json_decode($payload, true);

    if (
        !is_array($auth_data) ||
        !isset($auth_data["user_id"], $auth_data["user_name"]) ||
        (int) $auth_data["user_id"] <= 0
    ) {
        clear_auth_cookie();
        return;
    }

    $_SESSION["user_id"] = (int) $auth_data["user_id"];
    $_SESSION["user_name"] = (string) $auth_data["user_name"];
}

// Start the session on every page that needs login information.
start_app_session();
restore_auth_from_cookie();

function is_logged_in()
{
    return isset($_SESSION["user_id"]);
}

function require_login()
{
    if (!is_logged_in()) {
        clear_auth_state();
        $_SESSION["error"] = "Please log in first.";
        header("Location: login.php");
        exit();
    }
}

function redirect_if_logged_in()
{
    if (is_logged_in()) {
        header("Location: dashboard.php");
        exit();
    }
}

// Escape output before printing user data in HTML.
function e($value)
{
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

// Show a session message once, then remove it.
function show_message($type)
{
    if (isset($_SESSION[$type])) {
        $class_name = ($type === "success") ? "message success" : "message error";
        echo '<p class="' . $class_name . '">' . e($_SESSION[$type]) . '</p>';
        unset($_SESSION[$type]);
    }
}
?>
