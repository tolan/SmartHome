<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\Session;
use Slim\Views\Twig;
use Bluerhinos\phpMQTT;
use Predis\Client as RedisClient;
use SmartHome\Common\MQTT;
use SmartHome\Authorization\Authorize;
use SmartHome\Cache;
use SmartHome\DI;
use SmartHome\Event;
use SmartHome\Common\Utils;
use Doctrine\Common\Cache\PredisCache;

require 'vendor/autoload.php';

$config = require __DIR__.'/config.php';

const LOG_DIR = __DIR__;

$builder = new \DI\ContainerBuilder(DI\Container::class);
$builder->useAnnotations(true);
$container = $builder->build();

$container->set('session', function () {
    return new \SlimSession\Helper();
});

$container->set('settings', function() use($config) {
    return $config;
});

$container->set('logger', function() {
    $logger = new Logger('SmartHome');
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/app.log'));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/info.log', Logger::INFO));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/warning.log', Logger::WARNING));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/alert.log', Logger::ALERT));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/critical.log', Logger::CRITICAL));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/emergency.log', Logger::EMERGENCY));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/error.log', Logger::ERROR));
    return $logger;
});

$container->set('db', function(Container $container) {
    $stack = new \Doctrine\DBAL\Logging\DebugStack();

    $dbSettings = $container->get('settings')['db'];
    $logger = $container->get('logger'); /* @var $logger Logger */

    $redisSettings = $container->get('settings')['redis'];
    $redisClient = new RedisClient($redisSettings);

    $cacheDriver = new PredisCache($redisClient);
    $cacheDriver->setNamespace('db_cache_');

    $isDevMode = true;
    $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/server/Entity"), $isDevMode);
    $config->setQueryCacheImpl($cacheDriver);
    $config->setResultCacheImpl($cacheDriver);
    $config->setMetadataCacheImpl($cacheDriver);

    $connectionParams = array_merge($dbSettings, ['driver' => 'pdo_mysql']);
    $connection = DriverManager::getConnection($connectionParams, $config);

    $attempts = 3;
    while ($attempts) {
        try {
            $em = EntityManager::create($connection, $config);
            $em->getConfiguration()->setSQLLogger($stack);

            $connection = $em->getConnection();
            $connection->connect();
            if ($connection->isConnected()) {
                $attempts = 0;
            } else {
                $attempts--;
                if ($attempts === 0) {
                    $logger->error('There is a problem with connection to DB');
                }
            }
        } catch (\Exception $e) {
            $attempts--;
            $logger->warning('There is a problem with connection to DB: '.$e->getMessage());

            if ($attempts === 0) {
                $logger->error('There is a problem with connection to DB: '.$e->getMessage());
                throw $e;
            }
        }
    }

    return $em;
});

$container->set('view', function() {
    return Twig::create(__DIR__.'/public');
});

$id = $argv ? $argv[1].'-' : null;
$container->set('mqtt', function (Container $container) use ($id) {
    $settings = $container->get('settings')['mqtt'];
    $logger = $container->get('logger'); /* @var $logger Logger */

    $phpMqtt = new phpMQTT($settings['server'], $settings['port'], uniqid($settings['client_id'].$id));
    if (!$phpMqtt->connect(true, null, $settings['username'], $settings['password'])) {
        $logger->error('Error on connection to MQTT');
        exit(1);
    }

    return new MQTT($phpMqtt);
});

$container->set('cache', function (Container $container) {
    $settings = $container->get('settings')['redis'];

    $client = new RedisClient($settings);
    $adapter = new Cache\Adapter\Redis($client);
    $factory = new Cache\Factory($adapter);

    return $factory;
});

$container->set('authorize', function(Container $container) {
    return new Authorize($container);
});

$container->set(Event\Mediator::class, function (Container $container) {
    $mediator = new Event\Mediator();

    $listenersClasses = Utils\Path::getClasses(__DIR__.'/server/Event/Listeners');
    foreach ($listenersClasses as $listenerClass) {
        $refl = new ReflectionClass($listenerClass);
        if ($refl->isInstantiable()) {
            $listener = $container->get($listenerClass);
            $mediator->register($listener);
        }
    }

    return $mediator;
});

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->add(new Session($config['session']));
$app->addErrorMiddleware(true, true, true, $container->get('logger'));
$app->add(new ContentLengthMiddleware());
$app->add(new BodyParsingMiddleware());
