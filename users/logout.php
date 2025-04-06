<?php

session_start();

unset($_SESSION['user_logged_in']);
unset($_SESSION['user_name']);
unset($_SESSION['user_id']);

if (empty($_SESSION)) {
    session_destroy();
}

header('Location: login.php');
exit();
?>
