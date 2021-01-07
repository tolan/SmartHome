<?php

namespace SmartHome\Event\Listeners\Entity;

use SmartHome\Event\Abstracts;
use SmartHome\Event\Messages\Entity;

/**
 * This file defines abstract class for event entity listeners
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AListener extends Abstracts\AListener {

    /**
     * Returns that the message is enity update or delete
     *
     * @param Abstracts\AMessage $message Message
     *
     * @return bool
     */
    public function isAcceptableMessage(Abstracts\AMessage $message): bool {
        return in_array(get_class($message), [Entity\Update::class, Entity\Delete::class]);
    }

}
