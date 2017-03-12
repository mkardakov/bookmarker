<?php
date_default_timezone_set('UTC');

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/app/bootstrap.php';

$app->run();
