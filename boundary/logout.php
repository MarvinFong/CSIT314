<?php
require_once __DIR__ . '/User_Page.php';
require_once __DIR__ . '/../controller/Logout_Controller.php';

$page = new User_Page();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new Logout_Controller();
    $controller->LogoutUser();      // LogoutUser() -> end session
}
$page->RedirectLogin();             // RedirectLogin()
