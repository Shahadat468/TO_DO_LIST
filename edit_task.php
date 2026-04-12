<?php
require_once "auth.php";
require_once "db.php";

require_login();

$user_id = $_SESSION["user_id"];
$task = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $task_id = (int) ($_POST["task_id"] ?? 0);
    $task_text = clean_input($_POST["task_text"] ?? "");

    if ($task_id <= 0 || $task_text === "") {
        $_SESSION["error"] = "Please enter a valid task.";
        header("Location: dashboard.php");
        exit();
    }

    if (strlen($task_text) > 255) {
        $_SESSION["error"] = "Task text must be 255 characters or less.";
        header("Location: dashboard.php");
        exit();
    }

    // Confirm ownership before updating the task.
    $check_sql = "SELECT id FROM tasks WHERE id = ? AND user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "ii", $task_id, $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) !== 1) {
        $_SESSION["error"] = "Task not found or access denied.";
        header("Location: dashboard.php");
        exit();
    }

    // Update only if this task belongs to the current user.
    $update_sql = "UPDATE tasks SET task_text = ? WHERE id = ? AND user_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "sii", $task_text, $task_id, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION["success"] = "Task updated successfully.";
    } else {
        $_SESSION["error"] = "Task could not be updated.";
    }

    header("Location: dashboard.php");
    exit();
}

$task_id = (int) ($_GET["id"] ?? 0);

if ($task_id <= 0) {
    $_SESSION["error"] = "Invalid task selected.";
    header("Location: dashboard.php");
    exit();
}

// Load only the task that belongs to the current user.
$select_sql = "SELECT id, task_text FROM tasks WHERE id = ? AND user_id = ?";
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task - Multi-User To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card wide">
            <h1>Edit Task</h1>
            <p class="muted">Update your task text below.</p>

            <?php show_message("error"); ?>

            <form method="POST" action="edit_task.php">
                <input type="hidden" name="task_id" value="<?php echo $task["id"]; ?>">

                <label for="task_text">Task</label>
                <input type="text" id="task_text" name="task_text" value="<?php echo e($task["task_text"]); ?>" maxlength="255" required>

                <div class="form-actions">
                    <button type="submit" class="button primary">Update Task</button>
                    <a href="dashboard.php" class="button outline">Cancel</a>
                </div>
            </form>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>
