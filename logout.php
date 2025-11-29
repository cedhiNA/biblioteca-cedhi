<?php
require_once __DIR__ . '/app/auth.php';
require_once __DIR__ . '/app/paths.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//logout();
session_destroy();
setcookie('auth_token', '', time() - 3600, '/', '', true, true);
header("Location: " . url('index.php?logout=success'));
exit();
?>