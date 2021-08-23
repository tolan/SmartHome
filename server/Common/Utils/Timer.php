<?php

namespace SmartHome\Common\Utils;

use DI\Container;

/**
 * This file defines class for measure timing.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Timer {

    /**
     * Container
     *
     * @var Container
     */
    private $_container;

    /**
     * Start time
     *
     * @var float
     */
    private $_start;

    /**
     * Set of ticks
     *
     * @var array(float => string[])
     */
    private $_timers = [];

    /**
     * Set of markers
     *
     * @var array(string => string)
     */
    private $_markers = [];

    /**
     * Construct method for inject dependencies
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_container = $container;
        $this->clear();
    }

    /**
     * Marks next tick.
     *
     * @param string $name Name
     *
     * @return Timer
     */
    public function mark(string $name): Timer {
        $this->_markers[$name] = $name;

        return $this;
    }

    /**
     * Removes all timers, markers and initialize start time.
     *
     * @return Timer
     */
    public function clear(): Timer {
        $this->_start   = $this->_getTime();
        $this->_markers = [];
        $this->_timers  = [];

        return $this;
    }

    /**
     * Ticks current/given time.
     *
     * @param float $time Optional time
     *
     * @return Timer
     */
    public function tick(float $time = null): Timer {
        $this->_timers[($time ?? $this->_getTime())] = array_keys($this->_markers);
        $this->_markers                            = [];

        return $this;
    }

    /**
     * Flushes all timers with markers to log.
     *
     * @return Timer
     */
    public function flush(): Timer {
        $message = 'Data from timer.';
        $data    = [
            'all' => [],
        ];

        foreach ($this->_timers as $time => $markers) {
            $value         = (($time - $this->_start) / 1000);
            $data['all'][] = $value;
            foreach ($markers as $marker) {
                if (!$data[$marker]) {
                    $data[$marker] = [];
                }

                $data[$marker][] = $value;
            }
        }

        $this->_container->get('logger')->debug($message, ['timer' => $data]);

        return $this;
    }

    /**
     * Returns current time.
     *
     * @return float
     */
    private function _getTime(): float {
        return (microtime(true) * 1000 * 1000);
    }

    /**
     * Exports data to JSON.
     *
     * @param Timer $timer Instance of Timer
     *
     * @return string
     */
    public static function toJson(Timer $timer): string {
        $data = [
            'start'  => $timer->_start,
            'timers' => $timer->_timers,
        ];

        return JSON::encode($data);
    }

    /**
     * Import data from JSON to Timer instance.
     *
     * @param string $json  JSON data
     * @param Timer  $timer Timer instance
     *
     * @return Timer
     */
    public static function fromJson(string $json, Timer $timer): Timer {
        $timer->clear();

        $data = JSON::decode($json);

        $timer->_start  = $data['start'];
        $timer->_timers = $data['timers'];

        return $timer;
    }

}
