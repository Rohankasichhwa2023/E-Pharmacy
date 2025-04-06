<?php
session_start();

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_name']);


if (empty($_SESSION)) {
    session_destroy();
}

// Redirect to the admin login page
header('Location: login.php');
exit();
?>
