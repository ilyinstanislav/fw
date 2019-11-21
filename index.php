<?php

define('ROOT', dirname(__FILE__) . '/');
define('CORE', dirname(__FILE__) . '/core/');
define('API', dirname(__FILE__) . '/api/');
include CORE . 'main.php';
App::getInstance()->start();