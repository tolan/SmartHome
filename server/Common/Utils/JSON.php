<?php

namespace SmartHome\Common\Utils;

use Throwable;

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

    /**
     * Returns that the data are probably encoded.
     *
     * @param mixed $data Data
     *
     * @return bool
     */
    public static function isEncoded($data): bool {
        $is = false;
        try {
            if (is_string($data)) {
                self::decode($data);
                $is = json_last_error() === JSON_ERROR_NONE;
            }
        } catch (Throwable $e) {
            $is = false;
        }

        return $is;
    }

    /**
     * Returns that the dare are probably decoded.
     *
     * @param mixed $data Data
     *
     * @return bool
     */
    public static function isDecoded($data): bool {
        $is = false;
        try {
            self::encode($data);
            $is = json_last_error() === JSON_ERROR_NONE;
        } catch (Throwable $e) {
            $is = false;
        }

        return $is;
    }

}
