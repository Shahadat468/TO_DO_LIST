<?php
require_once "auth.php";
require_once "db.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$task_id = (int) ($_POST["task_id"] ?? 0);

if ($task_id <= 0) {
    $_SESSION["error"] = "Invalid task selected.";
    header("Location: dashboard.php");
    exit();
}

// Delete only if the task belongs to the current user.
$sql = "DELETE FROM tasks WHERE id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $task_id, $user_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION["success"] = "Task deleted successfully.";
} else {
    $_SESSION["error"] = "Task not found or access denied.";
}

header("Location: dashboard.php");
exit();
?>
