<?php

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use SmartHome\Common\MQTT;
use Seld\Signal\SignalHandler;
use DI\Container;
use SmartHome\Messaging\Abstracts\AWorker;

require_once __DIR__.'/../bootstrap.php'; /* @var $container Container */

$logger = $container->get('logger'); /* @var $logger Logger */
$db = $container->get('db'); /* @var $db EntityManager */
$mqtt = $container->get('mqtt'); /* @var $mqtt MQTT */

$signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGTERM], $logger);

$id = $argv[1];
$subscriber = $argv[2];

$container->set('taskId', $id);
$container->set('container', $container);
$worker = $container->get($subscriber); /* @var $worker AWorker */

try {
    while ($worker->proc()) {
        if ($db->getConnection()->ping() === false) {
            $logger->warning('Connection to DB for '.$subscriber.' will be reinitialized.');
            $db->getConnection()->close();
            $db->getConnection()->connect();
        }

        if ($signal->isTriggered()) {
            $logger->info('Subscriber '.$subscriber.' will be stopped.');
            break;
        }
    }
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), [$e]);
}

$mqtt->close();
$db->close();
