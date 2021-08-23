<?php

namespace SmartHome\Documents\Scheduler\Condition;

use SmartHome\Documents\Scheduler\Abstracts\ACondition;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * This file defines class for Ping condition.
 *
 * @ODM\Document
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Ping extends ACondition {

}
