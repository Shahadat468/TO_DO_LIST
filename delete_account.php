<?php
require_once "auth.php";
require_once "db.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$confirm_text = clean_input($_POST["confirm_text"] ?? "");

if ($confirm_text !== "DELETE") {
    $_SESSION["error"] = "Account was not deleted. Please type DELETE exactly.";
    header("Location: dashboard.php");
    exit();
}

// Deleting the user also deletes their tasks because database.sql uses ON DELETE CASCADE.
$sql = "DELETE FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    clear_auth_cookie();
    session_unset();
    session_destroy();

    start_app_session();
    $_SESSION["success"] = "Your account and tasks were deleted successfully.";
    header("Location: login.php");
    exit();
}

$_SESSION["error"] = "Account could not be deleted.";
header("Location: dashboard.php");
exit();
?>
