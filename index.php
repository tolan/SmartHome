<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use SmartHome\Common\Notification;
use SmartHome\Enum\HttpStatusCode;
use Selective\SameSiteCookie\SameSiteCookieConfiguration;
use Selective\SameSiteCookie\SameSiteCookieMiddleware;
use Selective\SameSiteCookie\SameSiteSessionMiddleware;
use Doctrine\ORM\EntityManager;

require_once './bootstrap.php';

/* @var $app \Slim\App */

$configuration = new SameSiteCookieConfiguration();
$configuration->secure = false;

// Register the samesite cookie middleware
$app->add(new SameSiteCookieMiddleware($configuration));

// Start the native PHP session handler and fetch the session attributes
$app->add(new SameSiteSessionMiddleware($configuration));

// Time logger
$app->add(function (Request $request, RequestHandler $handler) use ($container) {
    $start = microtime(true);

    $response = $handler->handle($request);
    $response = $response->withHeader('Access-Control-Allow-Origin', '*'); // TODO remove it on prod!!!

    $end = ((microtime(true) - $start) * 1000);
    $uri = $request->getUri();
    $route = $request->getAttribute('route'); /* @var $route \Slim\Route */

    $container->get('logger')->info('Request to "'.$uri.'" has taken '.round($end, 3).' ms.', ['pattern' => $route && $route->getPattern()]);

    return $response;
});

// ORM logger
$app->add(function (Request $request, RequestHandler $handler) use ($container) {
    /*  @var $container \SmartHome\DI\Container */
    $response = $handler->handle($request);

    if ($container->isCreated('db')) {
        $db = $container->get('db'); /* @var $db EntityManager */
        $uri = $request->getUri();
        $method = $request->getMethod();

        $stack = $db->getConfiguration()->getSQLLogger();

        $container->get('logger')->info('Request to "'.$method.': '.$uri.'" made '.count($stack->queries).' queries.', []);
    }

    return $response;
});

// Notification
$app->add(function(Request $request, RequestHandler $handler) {
    $request = Notification::cleanRequest($request);
    $response = $handler->handle($request);
    return $response;
});

// Default route
$app->add(function (Request $request, RequestHandler $handler) {
    $uri = $request->getUri();
    $path = $uri->getPath();

    if (strpos($path, '/api') !== 0 && $path !== '/') {
        if (file_exists(__DIR__.'/public'.$path)) {
            $response = new Response();
            $response = $response->withStatus(HttpStatusCode::MOVED_PERMANENTLY);
            $response = $response->withHeader('Location', '/public'.$path);
        } else {
            $request = $request->withUri($uri->withPath('/'));
            $response = $handler->handle($request);
        }
    } else {
        $response = $handler->handle($request);
    }

    return $response;
});

$sources = array_reduce(['Html', 'Rest'], function($acc, $path) {
    return array_merge($acc, glob(__DIR__.'/server/'.$path.'/*', GLOB_ONLYDIR));
}, []);

foreach ($sources as $dir) {
    foreach (['factory.php', 'router.php'] as $file) {
        $filename = $dir.'/'.$file;
        if (file_exists($filename)) {
            require_once $filename;
        }
    }
}

try {
    $app->run();
    $app->getContainer()->get('mqtt')->close();
    if ($app->getContainer()->isCreated('db')) {
        $db = $app->getContainer()->get('db'); /* @var $db EntityManager */
        $db->flush();
        $db->close();
    }
} catch (\Throwable $e) {
    $container->get('logger')->error('Something is wrong: '.$e->getMessage()."\n".$e->getTraceAsString(), []);
    throw $e;
}
