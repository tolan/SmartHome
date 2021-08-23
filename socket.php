<?php

// tick use required
declare(ticks=1);

use Seld\Signal\SignalHandler;
use Wrench\BasicServer;
use SmartHome\Socket\{
    App,
    Loop
};

require_once __DIR__.'/bootstrap.php';
require_once __DIR__.'/routes.php';

const SQL_QUERY_LOG = false;

/* @var $container DI\Container */

$logger = $container->get('logger'); /* @var $logger Logger */
$signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGTERM], $logger);
$config = $container->get('settings')['socket'];

try {
    $app  = new App($container);
    $loop = new Loop($signal, $container);

    $server = new BasicServer('ws://0.0.0.0:8000', $config);
    $server->registerApplication('app', $app);
    $server->setLoop($loop);

    $server->run();
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), [$e]);
}