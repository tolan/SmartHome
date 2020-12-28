<?php

namespace SmartHome\Event\Abstracts;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AListener {

    abstract function receive (AMessage $message);

    abstract function isAcceptableMessage (AMessage $message): bool;
}
