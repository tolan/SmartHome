<?php

namespace SmartHome\Event\Listeners\Entity;

use SmartHome\Event\Abstracts;
use SmartHome\Event\Messages\Entity;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AListener extends Abstracts\AListener {

    public function isAcceptableMessage (Abstracts\AMessage $message): bool {
        return in_array(get_class($message), [Entity\Update::class, Entity\Delete::class]);
    }

}
