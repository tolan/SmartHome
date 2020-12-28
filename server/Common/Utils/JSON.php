<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class JSON {

    public static function encode ($data, int $options = 0, int $depth = 512): string {
        return json_encode($data, $options, $depth);
    }

    public static function decode (string $json, bool $assoc = true, int $depth = 512, int $options = 0) {
        return json_decode($json, $assoc, $depth, $options);
    }

}
