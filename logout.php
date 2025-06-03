<?php
require_once 'includes/functions.php';

// Log out the user
logout();

// Redirect to login page
header("Location: login.php");
exit;
?>