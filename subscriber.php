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
use Seld\Signal\SignalHandler;
use Monolog\Logger;

require_once __DIR__.'/bootstrap.php';

/* @var $container DI\Container */

$logger = $container->get('logger'); /* @var $logger Logger */
$mqtt = $container->get('mqtt'); /* @var $mqtt MQTT */

$signal = SignalHandler::create([SignalHandler::SIGINT, SignalHandler::SIGTERM], $logger);

$subscribers = Utils\Path::getClasses(__DIR__.'/server/Messaging/Workers');

$taskContainer = new Container($logger);

foreach ($subscribers as $subscriber) {
    $task = new Task($mqtt, __DIR__.'/scripts/process-task.php', [$subscriber], str_replace('\\', '_', $subscriber));
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

while ($mqtt->proc() && $taskContainer->run(false)) {
    if ($signal->isTriggered()) {
        $logger->info('All processes will be stopped.');
        $taskContainer->stopAll();
        $logger->info('All processes has been stopped.');
        break;
    }

    usleep(100 * 1000);
}

$mqtt->close();
