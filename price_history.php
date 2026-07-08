<?php
// price_history.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ProductController.php';

$controller = new ProductController();
$controller->priceHistory();