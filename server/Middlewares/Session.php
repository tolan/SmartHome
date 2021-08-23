<?php

namespace SmartHome\Middlewares;

use Psr\Http\Server\{
    MiddlewareInterface,
    RequestHandlerInterface
};
use Psr\Http\Message\{
    ServerRequestInterface,
    ResponseInterface
};
use DI\Container;

/**
 * This file defines class for middleware of session.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Session implements MiddlewareInterface {

    /**
     * Container instance
     *
     * @var Container
     */
    private $_container;

    /**
     * Construct method for inject dependecy
     *
     * @param Container $container Container instance
     */
    public function __construct(Container $container) {
        $this->_container = $container;
    }

    /**
     * Process middleware
     *
     * @param ServerRequestInterface  $request Request instance
     * @param RequestHandlerInterface $handler Handler instance
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $this->_container->get('session');

        return $handler->handle($request);
    }

}
