<?php
require_once "auth.php";
require_once "db.php";

require_login();

$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];

$filter = $_GET["filter"] ?? "all";
if (!in_array($filter, ["all", "completed", "pending"])) {
    $filter = "all";
}

// Get task statistics for the logged-in user only.
$stats_sql = "SELECT
    COUNT(*) AS total_tasks,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_tasks,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_tasks
    FROM tasks
    WHERE user_id = ?";
$stats_stmt = mysqli_prepare($conn, $stats_sql);
mysqli_stmt_bind_param($stats_stmt, "i", $user_id);
mysqli_stmt_execute($stats_stmt);
$stats_result = mysqli_stmt_get_result($stats_stmt);
$stats = mysqli_fetch_assoc($stats_result);

$total_tasks = (int) $stats["total_tasks"];
$completed_tasks = (int) $stats["completed_tasks"];
$pending_tasks = (int) $stats["pending_tasks"];

// Get tasks for the logged-in user only, with an optional status filter.
if ($filter === "completed" || $filter === "pending") {
    $tasks_sql = "SELECT id, task_text, status, created_at FROM tasks WHERE user_id = ? AND status = ? ORDER BY created_at DESC";
    $tasks_stmt = mysqli_prepare($conn, $tasks_sql);
    mysqli_stmt_bind_param($tasks_stmt, "is", $user_id, $filter);
} else {
    $tasks_sql = "SELECT id, task_text, status, created_at FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
    $tasks_stmt = mysqli_prepare($conn, $tasks_sql);
    mysqli_stmt_bind_param($tasks_stmt, "i", $user_id);
}

mysqli_stmt_execute($tasks_stmt);
$tasks_result = mysqli_stmt_get_result($tasks_stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Multi-User To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="top-header">
        <div>
            <h1>Task Planner</h1>
            <p>Plan, finish, and track your personal tasks.</p>
        </div>
        <details class="profile-menu">
            <summary class="profile-button">
                <span class="profile-icon">✓</span>
                <span>
                    <strong><?php echo e($user_name); ?></strong>
                    <small>Task account</small>
                </span>
            </summary>

            <div class="profile-panel">
                <p class="profile-label">Signed in as</p>
                <p class="profile-name"><?php echo e($user_name); ?></p>

                <a href="logout.php" class="button outline full-menu-button">Logout</a>

                <details class="profile-danger">
                    <summary>Delete Account</summary>
                    <p>This deletes your task account and every task on your list.</p>
                    <form method="POST" action="delete_account.php" class="delete-account-form">
                        <label for="confirm_text">Type DELETE to confirm</label>
                        <input type="text" id="confirm_text" name="confirm_text" placeholder="DELETE" maxlength="6" required>
                        <button type="submit" class="button danger full-menu-button">Delete My Account</button>
                    </form>
                </details>
            </div>
        </details>
    </header>

    <main class="container">
        <?php show_message("error"); ?>
        <?php show_message("success"); ?>

        <section class="card">
            <h2>Write A New Task</h2>
            <form method="POST" action="add_task.php" class="task-form">
                <input type="text" name="task_text" placeholder="Example: Finish database lab report" maxlength="255" required>
                <button type="submit" class="button primary">Add Task</button>
            </form>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <span>All Tasks</span>
                <strong><?php echo $total_tasks; ?></strong>
            </div>
            <div class="stat-card">
                <span>Checked Off</span>
                <strong><?php echo $completed_tasks; ?></strong>
            </div>
            <div class="stat-card">
                <span>Still To Do</span>
                <strong><?php echo $pending_tasks; ?></strong>
            </div>
        </section>

        <section class="card">
            <div class="section-title-row">
                <h2>Your Checklist</h2>
                <div class="filters">
                    <a href="dashboard.php?filter=all" class="<?php echo ($filter === "all") ? "active" : ""; ?>">All</a>
                    <a href="dashboard.php?filter=completed" class="<?php echo ($filter === "completed") ? "active" : ""; ?>">Completed</a>
                    <a href="dashboard.php?filter=pending" class="<?php echo ($filter === "pending") ? "active" : ""; ?>">Pending</a>
                </div>
            </div>

            <?php if (mysqli_num_rows($tasks_result) === 0): ?>
                <p class="empty-state">Your checklist is empty. Add one task above to get started.</p>
            <?php else: ?>
                <div class="task-list">
                    <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
                        <article class="task-item <?php echo ($task["status"] === "completed") ? "done" : ""; ?>">
                            <span class="task-checkmark"><?php echo ($task["status"] === "completed") ? "✓" : "□"; ?></span>
                            <div class="task-main">
                                <p><?php echo e($task["task_text"]); ?></p>
                                <small>Added to list: <?php echo date("M d, Y h:i A", strtotime($task["created_at"])); ?></small>
                            </div>

                            <span class="badge <?php echo e($task["status"]); ?>">
                                <?php echo ucfirst(e($task["status"])); ?>
                            </span>

                            <div class="task-actions">
                                <a href="edit_task.php?id=<?php echo $task["id"]; ?>" class="button small">Edit</a>

                                <form method="POST" action="toggle_task.php" class="inline-form">
                                    <input type="hidden" name="task_id" value="<?php echo $task["id"]; ?>">
                                    <button type="submit" class="button small secondary">
                                        <?php echo ($task["status"] === "completed") ? "Mark Pending" : "Mark Completed"; ?>
                                    </button>
                                </form>

                                <form method="POST" action="delete_task.php" class="inline-form delete-task-form">
                                    <input type="hidden" name="task_id" value="<?php echo $task["id"]; ?>">
                                    <button type="submit" class="button small danger">Delete</button>
                                </form>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="script.js"></script>
</body>
</html>
