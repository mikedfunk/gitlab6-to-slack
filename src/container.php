<?php

use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;

// get a service container
$serviceContainer = new ContainerBuilder();

// register the http request context, url matcher, and controller resolver
$serviceContainer
    ->register(
        'web_hook_controller',
        'MikeFunk\Gitlab6ToSlack\Controllers\WebHookController'
    )
    ->addArgument(Request::createFromGlobals())
    ->addArgument(new Client())

    ->register(
        'file_locator',
        'Symfony\Component\Config\FileLocator'
    )->addArgument([__DIR__ . '/config'])
    ;
