<?php

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ApiController.php';

$controller = new ApiController();
$controller->getAiDescription();