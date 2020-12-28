<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class DateTime extends \DateTime {

    const FORMAT = 'Y-m-d\TH:i:s\Z';

    public function __toString() {
        return $this->format(self::FORMAT);
    }
}
