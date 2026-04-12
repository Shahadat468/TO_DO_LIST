<?php
require_once "auth.php";

// Clear the current session and start a fresh one for the logout message.
session_unset();
session_destroy();

session_start();
$_SESSION["success"] = "You have logged out successfully.";

header("Location: login.php");
exit();
?>
