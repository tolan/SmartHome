<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Doctrine\ORM;
use Doctrine\ODM;
use Doctrine\DBAL;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use Slim\Middleware\ContentLengthMiddleware;
use Slim\Middleware\Session;
use Bluerhinos\phpMQTT;
use Predis\Client as RedisClient;
use SmartHome\Common\MQTT;
use SmartHome\Authorization\Authorize;
use SmartHome\Cache;
use SmartHome\DI;
use SmartHome\Event;
use SmartHome\Common\Utils;
use Doctrine\Common\Cache\PredisCache;
use Elasticsearch\{
    ClientBuilder,
    Client
};
use Monolog\Handler\ElasticsearchHandler;
use SmartHome\Common\Utils\JSON;
use SmartHome\Middlewares;
use SlimSession\Cookie;

$loader = require 'vendor/autoload.php';

$config = require __DIR__.'/config.php';

date_default_timezone_set($config['timezone'] ?? 'Europe/Prague');

const LOG_DIR = __DIR__;
const MQTT_INTERVAL = 20 * 1000;
const DB_KEEP_ALIVE_INTERVAL = 3;

$builder   = new \DI\ContainerBuilder(DI\Container::class);
$builder->useAnnotations(true);
$container = $builder->build();

$container->set('settings', function() use($config) {
    return $config;
});

$container->set('timer', function (Container $container) {
    return new Utils\Timer($container);
});

$container->set('elastic', function(Container $container) {
    $elkSettings = $container->get('settings')['elk'];
    $client      = ClientBuilder::create()
            ->setHosts([$elkSettings['host'].':'.$elkSettings['port']])
            ->build();

    return $client;
});

$container->set('logger', function(Container $container) {
    $logger = new Logger('SmartHome');
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/info.log', Logger::INFO));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/warning.log', Logger::WARNING));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/alert.log', Logger::ALERT));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/critical.log', Logger::CRITICAL));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/emergency.log', Logger::EMERGENCY));
    $logger->pushHandler(new StreamHandler(__DIR__.'/logs/error.log', Logger::ERROR));

    try {
        $elastic = $container->get('elastic'); /* @var $elastic Client */
        $options = array(
            'index' => 'smarthome',
        );
        $handler = new class($elastic, $options, $logger) extends ElasticsearchHandler {

            private $_logger;

            public function __construct($elastic, $options, $logger) {
                parent::__construct($elastic, $options);
                $this->_logger = $logger;
            }

            protected function write(array $record): void {
                $message = 'Elastic is not working.';
                if ($record['message'] === $message) {
                    return;
                }

                try {
                    $this->bulkSend([$record['formatted']]);
                } catch (\Throwable $e) {
                    $this->_logger->error($message, [$e->getMessage()]);
                }
            }

        };
        $handler->pushProcessor(function (array $record) {
            $record['extra']['timer']   = $record['context']['timer'];
            $record['extra']['request'] = $_REQUEST;
            $record['extra']['server']  = array_filter($_SERVER, function ($_, $key) {
                return in_array($key, [
            'WEB_DOCUMENT_ROOT', 'SCRIPT_NAME', 'REQUEST_URI', 'QUERY_STRING', 'REQUEST_METHOD', 'SERVER_PROTOCOL', 'REQUEST_SCHEME', 'REMOTE_ADDR',
            'SERVER_NAME', 'HTTP_REFERER', 'HTTP_X_USER_LOGIN_TOKEN', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_USER_AGENT', 'HTTP_HOST', 'REDIRECT_STATUS'
                ]);
            }, ARRAY_FILTER_USE_BOTH);
            $record['context'] = JSON::encode($record['context']);

            return $record;
        });
        $logger->pushHandler($handler);
    } catch (\Throwable $e) {
        $logger->error('Init of elastic has failed..', [$e]);
    }

    return $logger;
});

$container->set('db', function(Container $container) {
    $stack = new \Doctrine\DBAL\Logging\DebugStack();

    $dbSettings = $container->get('settings')['db'];
    $logger     = $container->get('logger'); /* @var $logger Logger */

    $redisSettings = $container->get('settings')['redis'];
    $redisClient   = new RedisClient($redisSettings);

    $cacheDriver = new PredisCache($redisClient);
    $cacheDriver->setNamespace('db_cache_');

    $isDevMode = true;
    $config    = ORM\Tools\Setup::createAnnotationMetadataConfiguration(array(__DIR__."/server/Entity"), $isDevMode);
    $config->setQueryCacheImpl($cacheDriver);
    $config->setResultCacheImpl($cacheDriver);
    $config->setMetadataCacheImpl($cacheDriver);

    $connectionParams = array_merge($dbSettings, ['driver' => 'pdo_mysql']);
    $connection       = DBAL\DriverManager::getConnection($connectionParams, $config);

    $attempts = 3;
    while ($attempts) {
        try {
            $em = ORM\EntityManager::create($connection, $config);
            if (SQL_QUERY_LOG === true) {
                $stack = new DBAL\Logging\DebugStack();
                $em->getConfiguration()->setSQLLogger($stack);
            }

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

$container->set('mongo', function (Container $container) use ($loader) {
    \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader([$loader, 'loadClass']);

    $dbSettings    = $container->get('settings')['mongo'];
    $redisSettings = $container->get('settings')['redis'];
    $redisClient   = new RedisClient($redisSettings);

    $cacheDriver = new PredisCache($redisClient);
    $cacheDriver->setNamespace('mongo_cache_');

    $config = new ODM\MongoDB\Configuration();

    $dir = __DIR__.'/doctrine';
    $config->setProxyDir($dir.'/Proxies');
    $config->setProxyNamespace('Proxies');
    $config->setHydratorDir($dir.'/Hydrators');
    $config->setHydratorNamespace('Hydrators');

    $config->setMetadataDriverImpl(ODM\MongoDB\Mapping\Driver\AnnotationDriver::create(__DIR__.'/Documents'));

    $config->setMetadataCacheImpl($cacheDriver);

    $config->setDefaultDB($dbSettings['dbname']);

    spl_autoload_register($config->getProxyManagerConfiguration()->getProxyAutoloader());

    $uri    = 'mongodb://'.$dbSettings['user'].':'.$dbSettings['password'].'@'.$dbSettings['host'].':'.$dbSettings['port'].'/'.$dbSettings['dbname'];
    $client = new MongoDB\Client($uri, ['connect' => true], ['typeMap' => ODM\MongoDB\DocumentManager::CLIENT_TYPEMAP]);
    return ODM\MongoDB\DocumentManager::create($client, $config);
});

$id = $argv ? $argv[1].'-' : null;
$container->set('mqtt', function (Container $container) use ($id) {
    $settings = $container->get('settings')['mqtt'];
    $logger   = $container->get('logger'); /* @var $logger Logger */

    $phpMqtt = new phpMQTT($settings['server'], $settings['port'], uniqid($settings['client_id'].$id));
    if (!$phpMqtt->connect(true, null, $settings['username'], $settings['password'])) {
        $logger->error('Error on connection to MQTT');
        exit(1);
    }

    return new MQTT($phpMqtt, $container);
});

$container->set('cache', function (Container $container) {
    $settings = $container->get('settings')['redis'];

    $client  = new RedisClient($settings);
    $adapter = new Cache\Adapter\Redis($client);
    $factory = new Cache\Factory($adapter);

    return $factory;
});

$container->set('session', function (Container $container) {
    session_write_close();
    $key       = 'PHPSESSID';
    $sessionId = $_COOKIE[$key] ?? uniqid();

    $defaults = [
        'lifetime'     => '1 week',
        'path'         => '/',
        'domain'       => '',
        'secure'       => false,
        'httponly'     => false,
        'samesite'     => 'Lax',
        'name'         => $key,
        'autorefresh'  => false,
        'handler'      => null,
        'ini_settings' => [],
    ];

    Cookie::setup($defaults);
    if (isset($_COOKIE[$key])) {
        $expires = $defaults['lifetime'] + time();
        Cookie::set($key, $sessionId, $expires, $defaults);
    }

    return $container->get('cache')->getCache('session-'.$sessionId);
});

$container->set('authorize', function(Container $container) {
    return new Authorize($container);
});

$container->set(Event\Mediator::class, function (Container $container) {
    $mediator = new Event\Mediator();

    $listenersClasses = Utils\Path::getClasses(__DIR__.'/server/Event/Listeners', SmartHome\Event\Abstracts\AListener::class);
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
$app->add(new Middlewares\Session($container));
$app->addErrorMiddleware(true, true, true, $container->get('logger'));
$app->add(new ContentLengthMiddleware());
$app->add(new BodyParsingMiddleware());

$container->set('app', $app);
