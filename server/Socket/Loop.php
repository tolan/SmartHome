<?php

namespace SmartHome\Socket;

use Wrench\Util\LoopInterface;
use Seld\Signal\SignalHandler;
use DI\Container;

/**
 * This file defines class for socket loop.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Loop implements LoopInterface {

    /**
     * Time at last db check
     *
     * @var float
     */
    private $_lastCheck = null;

    /**
     * Instance of signal handler
     *
     * @var SignalHandler
     */
    private $_signal;

    /**
     * Container instance
     *
     * @var Container
     */
    private $_container;

    /**
     * Construct method for inject dependencies
     *
     * @param SignalHandler $signal    Signal handler instance
     * @param Container     $container Container
     */
    public function __construct(SignalHandler $signal, Container $container) {
        $this->_signal    = $signal;
        $this->_container = $container;
        $this->_lastCheck = microtime(true);
    }

    /**
     * Returns that the loop should continue
     *
     * @return bool
     */
    public function shouldContinue(): bool {
        if ($this->_container->isCreated('db') && (microtime(true) > ($this->_lastCheck + DB_KEEP_ALIVE_INTERVAL))) {
            $db = $this->_container->get('db'); /* @var $db EntityManager */
            if ($db->getConnection()->ping() === false) {
                $this->_container->get('logger')->warning('Connection to DB for socket will be reinitialized.');
                $db->getConnection()->close();
                $db->getConnection()->connect();
                $this->_lastCheck = microtime(true);
            }
        }

        if ($this->_signal->isTriggered()) {
            return false;
        }

        $this->_container->get('mqtt')->proc(false);

        gc_collect_cycles();
        usleep(1 * 1000);
        return true;
    }

}
