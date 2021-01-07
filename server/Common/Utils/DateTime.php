<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for work with Date time.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class DateTime extends \DateTime {

    const FORMAT = 'Y-m-d\TH:i:s\Z';

    /**
     * Returns date time in string format.
     *
     * @return string
     */
    public function __toString() {
        return $this->format(self::FORMAT);
    }

}
