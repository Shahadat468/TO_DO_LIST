<?php
require_once "auth.php";
require_once "db.php";

$name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = clean_input($_POST["name"] ?? "");
    $email = clean_input($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($name === "" || $email === "" || $password === "" || $confirm_password === "") {
        $_SESSION["error"] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["error"] = "Please enter a valid email address.";
    } elseif (strlen($name) > 100 || strlen($email) > 150) {
        $_SESSION["error"] = "Name or email is too long.";
    } elseif (strlen($password) < 6) {
        $_SESSION["error"] = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $_SESSION["error"] = "Passwords do not match.";
    } else {
        // Check if the email is already registered.
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "s", $email);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION["error"] = "This email is already registered.";
        } else {
            // Store only the hashed password, never the plain password.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $insert_sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "sss", $name, $email, $hashed_password);

            if (mysqli_stmt_execute($insert_stmt)) {
                $_SESSION["success"] = "Registration successful. Please log in.";
                header("Location: login.php");
                exit();
            } else {
                $_SESSION["error"] = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Multi-User To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="auth-page">
        <section class="auth-card">
            <h1>Create Account</h1>
            <p class="muted">Start managing your own private task list.</p>

            <?php show_message("error"); ?>
            <?php show_message("success"); ?>

            <form method="POST" action="register.php">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo e($name); ?>" maxlength="100" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo e($email); ?>" maxlength="150" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit" class="button primary full-width">Register</button>
            </form>

            <p class="auth-link">Already have an account? <a href="login.php">Log in</a></p>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>
