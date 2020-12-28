<?php

namespace SmartHome\Common\Abstracts;

use SmartHome\Common\MQTT;

abstract class Subscriber {

    /**
     *
     * @var MQTT
     */
    private $_mqtt;

    /**
     * @Inject({"mqtt"})
     */
    public function __construct ($mqtt) {
        $this->_mqtt = $mqtt;
    }

    final public function publish (string $topic, string $content, int $qos = 0, int $retain = 0): Subscriber {
        $this->_mqtt->publish($topic, $content, $qos, $retain);

        return $this;
    }

    public function getQos (): int {
        return 0;
    }

    public function getActiveTimeout (): int {
        return 30;
    }

    public function __invoke (string $topic, string $message) {
        return $this->subscribe($topic, $message);
    }

    abstract public function getTopic (): string;

    abstract public function subscribe (string $topic, string $message);
}
