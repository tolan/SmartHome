<?php

namespace SmartHome\Documents\Scheduler\Trigger;

use SmartHome\Documents\Scheduler\Abstracts\ATrigger;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * This file defines class for Time trigger.
 *
 * @ODM\Document
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Time extends ATrigger {

    /**
     * Returns identity message.
     *
     * @return string
     */
    public function getMessage(): string {
        return 'Typ: '.$this->getType().', Podtyp: '.$this->getData()['type'].'.';
    }

}
