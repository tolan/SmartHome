<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for ...
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Path {

    public static function getClasses (string $path): array {
        $path = rtrim($path, '/');
        $result = [];

        foreach (glob($path.'/*') as $filename) {
            if (is_dir($filename)) {
                $result = array_merge($result, self::getClasses($filename));
            } else {
                $filename = ltrim(rtrim($filename, '.php'), __DIR__.'/src/');
                $result[] = '\\SmartHome\\'.str_replace('/', '\\', $filename);
            }
        }

        return $result;
    }

}
