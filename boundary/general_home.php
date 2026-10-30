<?php
session_start();
require_once __DIR__ . '/User_Page.php';

// Any logged-in user whose profile has no dedicated home page lands here.
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$page = new User_Page();
$page->DisplayPage('Home');
