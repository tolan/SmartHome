<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for work with Path.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Path {

    /**
     * Returns classes in path and subdirectories
     *
     * @param string $path        Path
     * @param string $parentClass (optional) Parent classname
     *
     * @return array
     */
    public static function getClasses(string $path, string $parentClass): array {
        $path   = rtrim($path, '/');
        $result = [];

        foreach (glob($path.'/*') as $filename) {
            if (is_dir($filename)) {
                $result = array_merge($result, self::getClasses($filename, $parentClass));
            } else {
                $filename = ltrim(rtrim($filename, '.php'), __DIR__.'/src/');
                $class = '\\SmartHome\\'.str_replace('/', '\\', $filename);
                if (!$parentClass || is_subclass_of($class, $parentClass)) {
                    $result[] = $class;
                }
            }
        }

        return $result;
    }

}
