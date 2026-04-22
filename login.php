<?php
require_once "auth.php";
require_once "db.php";

$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = clean_input($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $_SESSION["error"] = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Please enter a valid email address.";
    } else {
        // Find the user by email, then verify the password hash.
        $sql = "SELECT id, name, password FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user["password"])) {
                session_regenerate_id(true);
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                set_auth_cookie($user["id"], $user["name"]);
                $_SESSION["success"] = "Welcome back, " . $user["name"] . "!";
                header("Location: dashboard.php");
                exit();
            } else {
                $_SESSION["error"] = "Incorrect password.";
            }
        } else {
            $_SESSION["error"] = "No account found with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Multi-User To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <h1>Login</h1>
            <p class="muted">Access your personal to-do list.</p>

            <?php show_message("error"); ?>
            <?php show_message("success"); ?>

            <form method="POST" action="login.php">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>" maxlength="150" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="button primary full-width">Log In</button>
            </form>

            <p class="auth-link">New user? <a href="register.php">Create an account</a></p>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>
