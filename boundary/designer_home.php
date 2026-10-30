<?php
session_start();
require_once __DIR__ . '/User_Page.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Interior Designer') {
    header("Location: login.php");
    exit;
}

$page = new User_Page();
$page->DisplayPage('Interior Designer Home');
