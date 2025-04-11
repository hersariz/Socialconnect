<?php
require_once '../includes/functions.php';

// Logout user
logoutUser();

// Redirect ke halaman login
header('Location: login.php');
exit;
?> 