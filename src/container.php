<?php

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;

// get a service container
$serviceContainer = new ContainerBuilder();

// register our only controller
$serviceContainer
    ->register(
        'web_hook_controller',
        'MikeFunk\Gitlab6ToSlack\Controllers\WebHookController'
    )
    ->addArgument(Request::createFromGlobals())
    ->addArgument(new Client());

return $serviceContainer;
