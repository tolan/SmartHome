<?php

namespace SmartHome\Scheduler;

use DI\Container;
use SmartHome\Cache\Storage;
use SmartHome\Enum;

/**
 * This file defines class for tracing and prevent cycling of scheduler.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Trace {

    /**
     * Storage
     *
     * @var Storage
     */
    private $_storage;

    /**
     * Trace ID
     *
     * @var string
     */
    private $_id;

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     * @param string    $traceId   Trace ID
     */
    public function __construct(Container $container, string $traceId) {
        $this->_storage = $container->get('cache')->getCache(Enum\Cache::SCOPE_SCHEDULER);
        $this->_id      = $traceId;
    }

    /**
     * Gets trace ID
     *
     * @return string
     */
    public function getId(): string {
        return $this->_id;
    }

    /**
     * Adds item
     *
     * @param mixed $id Item ID
     *
     * @return Trace
     */
    public function add($id): Trace {
        $triggered   = $this->_storage->has($this->_id) ? unserialize($this->_storage->get($this->_id)) : [];
        $triggered[] = $id;
        $this->_storage->set($this->_id, serialize($triggered), strtotime('1 day', 0));

        return $this;
    }

    /**
     * Returns whether the trace already contains item ID
     *
     * @param mixed $id Item ID
     *
     * @return bool
     */
    public function has($id): bool {
        $triggered = $this->_storage->has($this->_id) ? unserialize($this->_storage->get($this->_id)) : [];

        return in_array($id, $triggered);
    }

    /**
     * Generates new trace ID
     *
     * @return string
     */
    public static function getNewId(): string {
        return uniqid('trace_');
    }

}
