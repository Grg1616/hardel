<?php
ob_start();
session_start();
include 'includes/header.php';
include 'config.php';

// Authentication and Authorization Check
if (!isset($_SESSION['email']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}


include 'navs/navadmin.php';
?>