<?php
session_start();
$_SESSION = [];
session_destroy();
if (!empty($_COOKIE['remember'])) {
    setcookie('remember', '', time() - 3600, '/');
}
header('Location: home.php');
exit;
