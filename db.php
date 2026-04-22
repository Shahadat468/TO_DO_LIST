<?php
// Read database settings from environment variables for deployment,
// while keeping local XAMPP-friendly defaults.
$host = getenv("DB_HOST") ?: "localhost";
$port = (int) (getenv("DB_PORT") ?: 3306);
$user = getenv("DB_USER") ?: "root";
$database = getenv("DB_NAME") ?: "todo_multiuser_system";
$password_value = getenv("DB_PASSWORD");
$password = ($password_value === false) ? "" : $password_value;
$ssl_ca_text = getenv("DB_SSL_CA") ?: "";

$conn = mysqli_init();
$ssl_flag = 0;

if ($ssl_ca_text !== "") {
    $ssl_ca_text = trim($ssl_ca_text);
    $ssl_ca_text = str_replace(["\\r\\n", "\\n", "\\r"], "\n", $ssl_ca_text);
    $ssl_ca_text = str_replace(["\r\n", "\r"], "\n", $ssl_ca_text);
    $ssl_ca_text = rtrim($ssl_ca_text, "\n") . "\n";

    $ssl_ca_file = sys_get_temp_dir() . "/aiven-ca.pem";
    file_put_contents($ssl_ca_file, $ssl_ca_text, LOCK_EX);
    mysqli_ssl_set($conn, null, null, $ssl_ca_file, null, null);
    $ssl_flag = MYSQLI_CLIENT_SSL;
}

if (!$conn || !mysqli_real_connect($conn, $host, $user, $password, $database, $port, null, $ssl_flag)) {
    die("Database connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Basic input cleanup before using values in validation or prepared statements.
function clean_input($data)
{
    return trim($data);
}
?>
