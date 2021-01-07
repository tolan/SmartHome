<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for work with JSON.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class JSON {

    /**
     * Encodes to JSON
     *
     * @param mixed $data    Data
     * @param int   $options Options
     * @param int   $depth   Depth
     *
     * @return string
     */
    public static function encode($data, int $options = 0, int $depth = 512): string {
        return json_encode($data, $options, $depth);
    }

    /**
     * Decodes from JSON
     *
     * @param string $json    JSON string
     * @param bool   $assoc   Return as associative array
     * @param int    $depth   Depth
     * @param int    $options Options
     *
     * @return mixed
     */
    public static function decode(string $json, bool $assoc = true, int $depth = 512, int $options = 0) {
        return json_decode($json, $assoc, $depth, $options);
    }

}
