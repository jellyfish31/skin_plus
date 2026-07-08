<?php
// admin_crud.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/AdminController.php';

$controller = new AdminController();
$controller->crud();