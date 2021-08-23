<?php

namespace SmartHome\Event\Listeners;

use SmartHome\Event\Abstracts\{
    AListener,
    AMessage
};
use SmartHome\Common\{
    MQTT,
    Utils\JSON
};
use SmartHome\Enum\Topic;
use SmartHome\Entity\Timer;
use DI\Container;

/**
 * This file defines class for listen to messaging.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Messaging extends AListener {

    /**
     * MQTT client
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * Construct method for inject container
     *
     * @param Container $container Container
     */
    public function __construct(Container $container) {
        $this->_mqtt = $container->get('mqtt');
    }

    /**
     * Returns that the message is acceptable by listener.
     *
     * @param AMessage $message Message
     *
     * @return bool
     */
    public function isAcceptableMessage(AMessage $message): bool {
        if ($message->getData() instanceof Timer) {
            return false;
        }

        return true;
    }

    /**
     * Receives message
     *
     * @param AMessage $message Message
     *
     * @return void
     */
    public function receive(AMessage $message): void {
        $this->_mqtt->publish(Topic::EVENT_MESSAGE, JSON::encode($message), 0, 0, true);
    }

}
