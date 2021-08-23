<?php

namespace SmartHome\Scheduler\Actions;

use Exception;
use SmartHome\Scheduler\Abstracts\AAction;
use SmartHome\Common;

/**
 * This file defines class for MQTT action.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Mqtt extends AAction {

    /**
     * Performs MQTT publish action.
     *
     * @return bool
     */
    public function execute(): bool {
        $data     = $this->getAction()->getData();
        ['topic' => $topic, 'message' => $message] = $data;

        $success = true;
        try {
            $mqtt = $this->getContext()->getContainer()->get('mqtt'); /* @var $mqtt Common\MQTT */
            $mqtt->publish($this->translateValue($topic), $this->translateValue($message));
        } catch (Exception $e) {
            $success = false;
            $this->getContext()->getContainer()->get('logger')->error('Error in HTTP action: '.$e->getMessage(), [$e]);
        }

        return $success;
    }

    /**
     * Returns whether the actions are executable
     *
     * @return bool
     */
    public function isExecutable(): bool {
        return true;
    }

}
