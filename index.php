<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
} else {
    redirect('dashboard.php');
}
?>