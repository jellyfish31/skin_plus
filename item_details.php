<?php
// item_details.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ProductController.php';

$controller = new ProductController();
$controller->details();