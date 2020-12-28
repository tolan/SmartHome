<?php

namespace SmartHome\Event;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Mediator {

    /**
     *
     * @var Abstracts\AListener[]
     */
    private $_listeners = [];

    public function register (Abstracts\AListener $listener): Mediator {
        $this->_listeners[] = $listener;

        return $this;
    }

    public function send (Abstracts\AMessage $message): Mediator {
        foreach ($this->_listeners as $listener) {
            if ($listener->isAcceptableMessage($message)) {
                $listener->receive($message);
            }
        }

        return $this;
    }

}
