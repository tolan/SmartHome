<?php

use SmartHome\Common\{
    Utils,
    MQTT
};
use SmartHome\Process\{
    Container,
    Task,
    Exception
};
use SmartHome\Enum\{
    Cache,
    Topic,
};
use SmartHome\Cache\Storage;
use SmartHome\Messaging\Abstracts\AWorker;
use Seld\Signal\SignalHandler;
use Monolog\Logger;

require_once __DIR__.'/bootstrap.php';

const PROCESS_STATE_INTERVAL = 1000;

/* @var $container DI\Container */

$logger = $container->get('logger'); /* @var $logger Logger */
$mqtt   = $container->get('mqtt'); /* @var $mqtt MQTT */
$cache  = $container->get('cache')->getCache(Cache::SCOPE_PROCESS); /* @var $cache Storage */

gc_enable();

$signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGTERM], $logger);

$subscribers = Utils\Path::getClasses(__DIR__.'/server/Messaging/Workers', AWorker::class);

$taskContainer = new Container($logger);

foreach ($subscribers as $subscriber) {
    $task     = new Task($mqtt, __DIR__.'/scripts/process-task.php', [$subscriber], str_replace('\\', '_', $subscriber));
    $attempts = 3;
    do {
        try {
            $task->init();
            $attempts = 0;
        } catch (Exception $ex) {
            $logger->error('Init of task '.$subscriber.' has failed.', [$ex]);
            $mqtt->reconnect();
            if (!($attempts--)) {
                throw $ex;
            }
        }
    } while ($attempts);

    $task->setActiveTimeout($subscriber::ACTIVE_TIMEOUT);
    $taskContainer->addTask($task);
}

$lastProcessSent = null;
while ($mqtt->proc(false) && $taskContainer->run(false)) {
    if ($signal->isTriggered()) {
        $logger->info('All processes will be stopped.');
        $taskContainer->stopAll();
        $logger->info('All processes has been stopped.');
        break;
    }

    $time         = microtime(true) * 1000;
    $currentState = $taskContainer->getTasksInfo();
    if ($time - $lastProcessSent > PROCESS_STATE_INTERVAL) {
        $encoded = Utils\JSON::encode($currentState);
        $mqtt->publish(Topic::PROCESS_INFO, $encoded);
        $cache->set('statusInfo', $encoded);
        $lastProcessSent = $time;
    }

    gc_collect_cycles();
    usleep(MQTT_INTERVAL);
}

$mqtt->close();
