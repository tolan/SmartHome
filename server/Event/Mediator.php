<?php

namespace SmartHome\Event;

/**
 * This file defines class for event mediator
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Mediator {

    /**
     * List of registred listeners
     *
     * @var Abstracts\AListener[]
     */
    private $_listeners = [];

    /**
     * Register listener
     *
     * @param Abstracts\AListener $listener Listener
     *
     * @return Mediator
     */
    public function register(Abstracts\AListener $listener): Mediator {
        $this->_listeners[] = $listener;

        return $this;
    }

    /**
     * Sends message to registered listeners
     *
     * @param Abstracts\AMessage $message Message
     *
     * @return Mediator
     */
    public function send(Abstracts\AMessage $message): Mediator {
        foreach ($this->_listeners as $listener) {
            if ($listener->isAcceptableMessage($message)) {
                $listener->receive($message);
            }
        }

        return $this;
    }

}
