<?php

namespace SmartHome\Documents\Scheduler\Trigger;

use SmartHome\Documents\Scheduler\Abstracts\ATrigger;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * This file defines class for Event trigger.
 *
 * @ODM\Document
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Event extends ATrigger {

    /**
     * Returns identity message.
     *
     * @return string
     */
    public function getMessage(): string {
        return 'Typ: '.$this->getType().', Událost: '.$this->getData()['type'].'.';
    }

}
