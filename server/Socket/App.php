<?php

namespace SmartHome\Socket;

use Psr\Http\Message\ResponseInterface;
use DI\Container;
use Wrench\{
    Application,
    Connection
};
use Slim\Routing\RoutingResults;
use Slim\Factory\ServerRequestCreatorFactory;
use SmartHome\Common\{
    MQTT,
    Utils\JSON,
    Utils\Timer,
    Service
};
use SmartHome\Enum\{
    SocketEventType,
    Topic,
    HttpStatusCode
};
use SmartHome\Service\User;

/**
 * This file defines class for socket app.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class App implements Application\DataHandlerInterface, Application\ConnectionHandlerInterface {

    const CONNECTION   = 'connection';
    const SUBSCRIPTION = 'subscription';

    /**
     * Container instance
     *
     * @var Container
     */
    private $_container;

    /**
     * MQTT instance
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * List of clients connections
     *
     * @var array
     */
    private $_connections = [];

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_container = $container;
        $this->_mqtt      = $container->get('mqtt');

        $subscribers        = [
            Topic::EVENT_MESSAGE => [
                'qos'      => 0,
                'function' => function (string $topic, string $message) {
                    $this->notify(SocketEventType::MESSAGE, JSON::decode($message), [SocketEventType::MESSAGE]);
                },
            ],
            Topic::PROCESS_INFO => [
                'qos'      => 0,
                'function' => function (string $topic, string $message) {
                    $this->notify(SocketEventType::PROCESS_STATES, JSON::decode($message), [SocketEventType::PROCESS_STATES]);
                },
            ],
        ];
        $this->_mqtt->subscribe($subscribers);
    }

    /**
     * Handle data received from a client
     *
     * @param string     $message    Message
     * @param Connection $connection Client connection
     *
     * @return void
     */
    public function onData(string $message, Connection $connection): void {
        ['type' => $type, 'data' => $data] = JSON::decode($message);

        $answer = null;
        switch ($type) {
            case SocketEventType::KEEP_ALIVE:
                $answer = JSON::decode($message);
                break;
            case SocketEventType::REQUEST:
                $answer = $this->_sendRequest($data);
                break;
            case SocketEventType::SUBSCRIBE:
                $answer = $this->_subscribe($data, $connection);
                break;
            case SocketEventType::UNSUBSCRIBE:
                $answer = $this->_unSubscribe($data, $connection);
                break;
        }

        $connection->send(JSON::encode($answer));
    }

    /**
     * Handle incomming client connection
     *
     * @param Connection $connection Client connection
     *
     * @return void
     */
    public function onConnect(Connection $connection): void {
        $this->_connections[$connection->getId()] = [
            self::CONNECTION   => $connection,
            self::SUBSCRIPTION => [],
        ];
    }

    /**
     * Hanlde client disconnection
     *
     * @param Connection $connection Client connection
     *
     * @return void
     */
    public function onDisconnect(Connection $connection): void {
        unset($this->_connections[$connection->getId()]);
    }

    /**
     * Notify connected clients
     *
     * @param string $type  Message type
     * @param array  $data  Data
     * @param array  $flags Flag for subscription
     *
     * @return void
     */
    public function notify(string $type, array $data, array $flags = []) {
        foreach ($this->_connections as $connection) {
            if (empty($flags) || !empty(array_intersect($flags, $connection[self::SUBSCRIPTION]))) {
                $connection['connection']->send(JSON::encode(['type' => $type, 'data' => $data]));
            }
        }
    }

    /**
     * Subscribe to notification
     *
     * @param string     $type       Message type
     * @param Connection $connection Client connection
     *
     * @return boolean
     */
    private function _subscribe(string $type, Connection $connection) {
        foreach ($this->_connections as $key => $item) {
            if ($item[self::CONNECTION] === $connection) {
                $item[self::SUBSCRIPTION][]                   = $type;
                $this->_connections[$key][self::SUBSCRIPTION] = array_unique($item[self::SUBSCRIPTION]);
            }
        }

        return true;
    }

    /**
     * Unsubscribe from notification
     *
     * @param string     $type       Message type
     * @param Connection $connection Client connection
     *
     * @return boolean
     */
    private function _unSubscribe(string $type, Connection $connection) {
        foreach ($this->_connections as $key => $item) {
            if ($item[self::CONNECTION] === $connection) {
                $this->_connections[$key][self::SUBSCRIPTION] = array_filter($item[self::SUBSCRIPTION], function($subs) use ($type) {
                    return $subs !== $type;
                });
            }
        }

        return true;
    }

    /**
     * Sends request to rest of application (resolved by method, uri and body)
     *
     * @param array $data Data with method, uri and body
     *
     * @return ?ResponseInterface
     */
    private function _sendRequest(array $data) {
        [
            'method' => $method,
            'uri' => $uri,
            'body' => $body,
            'X-User-Login-Token' => $userToken,
            'X-date' => $date,
        ] = $data;

        $container   = $this->_container;
        $timer       = $container->get('timer'); /* @var $timer Timer */
        $app         = $container->get('app'); /* @var $app \Slim\App */
        $userService = $container->get(User::class); /* @var $userService User */

        $timer->mark('socket')->mark('request')->tick($date);

        $response = null;
        if ($userService->refreshLogin($userToken)) {
            $routeResults = $app->getRouteResolver()->computeRoutingResults($uri, $method);
            if ($routeResults->getRouteStatus() === RoutingResults::FOUND) {
                $serverRequestCreator = ServerRequestCreatorFactory::create();
                $request              = $serverRequestCreator->createServerRequestFromGlobals();
                $request              = $request->withMethod($method)
                    ->withUri($request->getUri()->withPath($uri))
                    ->withParsedBody($body);

                $response = $app->handle($request);
            } else {
                $response = $app->getResponseFactory()->createResponse(HttpStatusCode::BAD_REQUEST)->withBody('Invalid route.');
            }
        } else {
            $response = $app->getResponseFactory()->createResponse(HttpStatusCode::BAD_REQUEST)->withBody('Invalid user token.');
        }

        if ($container->isCreated(Service::class)) {
            $container->get(Service::class)->flush()->clear();
        }

        $timer->flush()->clear();

        return $response;
    }

}
