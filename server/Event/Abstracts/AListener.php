<?php

namespace SmartHome\Event\Abstracts;

/**
 * This file defines abstract class for event listener.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AListener {

    /**
     * Receives message
     *
     * @param AMessage $message Message
     *
     * @return void
     */
    abstract public function receive(AMessage $message);

    /**
     * Returns that the message is acceptable by listener.
     *
     * @param AMessage $message Message
     *
     * @return bool
     */
    abstract public function isAcceptableMessage(AMessage $message): bool;

}
