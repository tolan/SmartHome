<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use SmartHome\Common\{
    Notification,
    Utils\Timer
};
use SmartHome\Enum\HttpStatusCode;
use Selective\SameSiteCookie\SameSiteCookieConfiguration;
use Selective\SameSiteCookie\SameSiteCookieMiddleware;
use Selective\SameSiteCookie\SameSiteSessionMiddleware;
use Doctrine\ORM\EntityManager;

const SQL_QUERY_LOG = true;

require_once './bootstrap.php';

/* @var $app \Slim\App */

$timer  = $app->getContainer()->get('timer'); /* @var $timer Timer */
$logger = $app->getContainer()->get('logger');

$configuration         = new SameSiteCookieConfiguration();
$configuration->secure = false;

// Register the samesite cookie middleware
$app->add(new SameSiteCookieMiddleware($configuration));

// Start the native PHP session handler and fetch the session attributes
$app->add(new SameSiteSessionMiddleware($configuration));

// Time logger
$app->add(function (Request $request, RequestHandler $handler) use ($timer, $logger) {
    $timer->mark('index.time.start')->tick();
    $start = microtime(true);

    $response = $handler->handle($request);
    $timer->mark('index.time.handle.after')->tick();
    $response = $response->withHeader('Access-Control-Allow-Origin', '*'); // TODO remove it on prod!!!
    $timer->mark('index.time.header.after')->tick();

    $end = ((microtime(true) - $start) * 1000);
    if ($end > 500) {
        $uri     = $request->getUri();
        $timer->mark('index.time.getUri.after')->tick();
        $route   = $request->getAttribute('route'); /* @var $route \Slim\Route */
        $pattern = $route && $route->getPattern();
        $timer->mark('index.time.getRoute.after')->tick();

        $logger->info('Request to "'.$uri.'" has taken '.round($end, 3).' ms.', ['uri' => $uri, 'pattern' => $pattern]);
    }
    $timer->mark('index.time.end')->tick();

    return $response;
});

// ORM logger
$app->add(function (Request $request, RequestHandler $handler) use ($timer, $container) {
    $timer->mark('index.orm.logger.start')->tick();
    /*  @var $container \SmartHome\DI\Container */
    $response = $handler->handle($request);
    $timer->mark('index.orm.logger.handle.after')->tick();

    if ($container->isCreated('db')) {
        $db     = $container->get('db'); /* @var $db EntityManager */
        $uri    = $request->getUri();
        $method = $request->getMethod();

        $stack   = $db->getConfiguration()->getSQLLogger();
        $queries = $stack ? count($stack->queries) : 0;

        if ($queries > 0) {
            $container->get('logger')->info('Request to "'.$method.': '.$uri.'" made '.$queries.' queries.', []);
        }
    }
    $timer->mark('index.orm.logger.end')->tick();

    return $response;
});

// Notification
$app->add(function(Request $request, RequestHandler $handler) use($timer) {
    $timer->mark('index.notification.start')->tick();
    $request  = Notification::cleanRequest($request);
    $response = $handler->handle($request);
    $timer->mark('index.notification.end')->tick();
    return $response;
});

// Default route
$app->add(function (Request $request, RequestHandler $handler) use($timer) {
    $timer->mark('index.default.route.start')->tick();
    $uri  = $request->getUri();
    $path = $uri->getPath();

    if (strpos($path, '/api') !== 0 && $path !== '/') {
        if (file_exists(__DIR__.'/public'.$path)) {
            $response = new Response();
            $response = $response->withStatus(HttpStatusCode::MOVED_PERMANENTLY);
            $response = $response->withHeader('Location', '/public'.$path);
        } else {
            $request  = $request->withUri($uri->withPath('/'));
            $response = $handler->handle($request);
        }
    } else {
        $response = $handler->handle($request);
    }

    $timer->mark('index.default.route.end')->tick();
    return $response;
});

require_once './routes.php';

try {
    $timer->mark('index.start')->tick();

    $app->run();

    $timer->mark('index.after.run')->tick();

    $app->getContainer()->get('mqtt')->close();

    $timer->mark('index.after.mqtt.close')->tick();

    if ($app->getContainer()->isCreated('db')) {
        $db = $app->getContainer()->get('db'); /* @var $db EntityManager */
        $db->flush();
        $db->close();
    }

    $timer->mark('index.end')->tick()->flush()->clear();
} catch (\Throwable $e) {
    $container->get('logger')->error('Something is wrong: '.$e->getMessage()."\n".$e->getTraceAsString(), []);
    throw $e;
}
