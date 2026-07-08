<?php
// ai_image_matcher.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/controllers/ApiController.php';

$controller = new ApiController();
$controller->aiImageMatcher();