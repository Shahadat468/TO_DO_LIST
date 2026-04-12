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

// First get the current status, but only for the logged-in user's task.
$select_sql = "SELECT status FROM tasks WHERE id = ? AND user_id = ?";
$select_stmt = mysqli_prepare($conn, $select_sql);
mysqli_stmt_bind_param($select_stmt, "ii", $task_id, $user_id);
mysqli_stmt_execute($select_stmt);
$select_result = mysqli_stmt_get_result($select_stmt);

if (mysqli_num_rows($select_result) !== 1) {
    $_SESSION["error"] = "Task not found or access denied.";
    header("Location: dashboard.php");
    exit();
}

$task = mysqli_fetch_assoc($select_result);
$new_status = ($task["status"] === "completed") ? "pending" : "completed";

// Update only the logged-in user's task.
$update_sql = "UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "sii", $new_status, $task_id, $user_id);

if (mysqli_stmt_execute($update_stmt)) {
    $_SESSION["success"] = "Task status updated.";
} else {
    $_SESSION["error"] = "Failed to update task status.";
}

header("Location: dashboard.php");
exit();
?>
