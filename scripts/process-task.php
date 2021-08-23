<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Monolog\Logger;
use SmartHome\Common\MQTT;
use Seld\Signal\SignalHandler;
use SmartHome\DI\Container;
use SmartHome\Messaging\Abstracts\AWorker;

const SQL_QUERY_LOG = false;

require_once __DIR__.'/../bootstrap.php'; /* @var $container Container */

$logger = $container->get('logger'); /* @var $logger Logger */
$db = $container->get('db'); /* @var $db EntityManager */

gc_enable();

$signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGTERM], $logger);

$id         = $argv[1];
$subscriber = $argv[2];

$container->set('taskId', $id);
$container->set('container', $container);
$worker = $container->get($subscriber); /* @var $worker AWorker */

$elastic = $container->get('elastic'); /* @var $elastic \Elasticsearch\Client */

$lastCheck = microtime(true);

try {
    while ($worker->proc()) {
        if ($container->isCreated('db') && (microtime(true) > ($lastCheck + DB_KEEP_ALIVE_INTERVAL))) {
            $db = $container->get('db'); /* @var $db EntityManager */
            if ($db->getConnection()->ping() === false) {
                $logger->warning('Connection to DB for '.$subscriber.' will be reinitialized.');
                $db->getConnection()->close();
                $db->getConnection()->connect();
                $lastCheck = microtime(true);
            }
        }

        if ($signal->isTriggered()) {
            $logger->info('Subscriber '.$subscriber.' will be stopped.');
            break;
        }

        gc_collect_cycles();
        usleep(MQTT_INTERVAL);
    }
} catch (\Throwable $e) {
    $logger->error($e->getMessage(), [$e]);
}

if ($container->isCreated('mongo')) {
    $mongo = $container->get('mongo'); /* @var $mongo DocumentManager */
    $mongo->close();
}

if ($container->isCreated('mqtt')) {
    $mqtt = $container->get('mqtt'); /* @var $mqtt MQTT */
    $mqtt->close();
}

$db->close();
