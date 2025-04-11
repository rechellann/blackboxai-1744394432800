<?php
require_once '../includes/functions.php';

// Destroy the session
session_destroy();

// Set logout message
session_start();
$_SESSION['alert'] = [
    'message' => 'You have been successfully logged out.',
    'type' => 'success'
];

// Redirect to login page
header('Location: /index.php');
exit();
?>
