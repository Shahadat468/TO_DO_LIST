<?php
function start_app_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $is_https = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");

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
        "secure" => $is_https,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

// Start the session on every page that needs login information.
start_app_session();

function is_logged_in()
{
    return isset($_SESSION["user_id"]);
}

function require_login()
{
    if (!is_logged_in()) {
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
