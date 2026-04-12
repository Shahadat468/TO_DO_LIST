<?php
// Start the session on every page that needs login information.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
