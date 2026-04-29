<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    log_logout($conn, $_SESSION['user_id'], $_SESSION['username']);
}

session_destroy();
redirect('login.php');
?>