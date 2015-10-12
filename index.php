<?php
date_default_timezone_set("Europe/Kiev");

defined('ROOT_PATH') || define('ROOT_PATH', '/');
defined('BASE_PATH') || define('BASE_PATH', __DIR__ . '/');
defined('CONFIG') || define('CONFIG', 'config/');

require_once __DIR__ . '/vendor/autoload.php';

\Core\Bootstrap::init();
