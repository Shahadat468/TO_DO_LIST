<?php
// Read database settings from environment variables for deployment,
// while keeping local XAMPP-friendly defaults.
$host = getenv("DB_HOST") ?: "localhost";
$port = (int) (getenv("DB_PORT") ?: 3306);
$user = getenv("DB_USER") ?: "root";
$database = getenv("DB_NAME") ?: "todo_multiuser_system";
$password_value = getenv("DB_PASSWORD");
$password = ($password_value === false) ? "" : $password_value;

$conn = mysqli_init();

if (!$conn || !mysqli_real_connect($conn, $host, $user, $password, $database, $port)) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Basic input cleanup before using values in validation or prepared statements.
function clean_input($data)
{
    return trim($data);
}
?>
