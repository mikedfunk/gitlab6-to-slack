<?php

require_once __DIR__ . '/../vendor/autoload.php';

// load environment vars from .env file
Dotenv::load(__DIR__);

// get service container with services registered
$serviceContainer = include __DIR__ . '/../src/container.php';

// send the request through
$response = $serviceContainer->get('web_hook_controller')
    ->indexAction();
$response->send();
