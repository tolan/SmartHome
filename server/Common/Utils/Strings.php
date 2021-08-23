<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for work with Strings.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Strings {

    /**
     * Converts from dash
     *
     * @param string $string String in dash format
     *
     * @return string
     */
    public static function fromDash($string) {
        return ucfirst(str_replace('-', ' ', $string));
    }

    /**
     * Converts from underscore
     *
     * @param string $string String in underscore format
     *
     * @return string
     */
    public static function fromUnderscore($string) {
        return ucfirst(str_replace('_', ' ', $string));
    }

    /**
     * Converts from camel case to underscore
     *
     * @param string $string String in camel case format
     *
     * @return string
     */
    public static function fromCamelCaseToUnderscore($string) {
        return join('_', preg_split('/(?=[A-Z])/', $string));
    }

}
