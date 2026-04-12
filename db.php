<?php
// Database connection for XAMPP on macOS.
$host = "localhost";
$user = "root";
$password = "";
$database = "todo_multiuser_system";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Basic input cleanup before using values in validation or prepared statements.
function clean_input($data)
{
    return trim($data);
}
?>
