<?php
require_once "auth.php";
require_once "db.php";

require_login();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$task_text = clean_input($_POST["task_text"] ?? "");

if ($task_text === "") {
    $_SESSION["error"] = "Task text cannot be empty.";
    header("Location: dashboard.php");
    exit();
}

if (strlen($task_text) > 255) {
    $_SESSION["error"] = "Task text must be 255 characters or less.";
    header("Location: dashboard.php");
    exit();
}

// Add the task for the current logged-in user only.
$sql = "INSERT INTO tasks (user_id, task_text, status) VALUES (?, ?, 'pending')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "is", $user_id, $task_text);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION["success"] = "Task added successfully.";
} else {
    $_SESSION["error"] = "Failed to add task.";
}

header("Location: dashboard.php");
exit();
?>
