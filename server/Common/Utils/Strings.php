<?php

namespace SmartHome\Common\Utils;

class Strings {

    public static function fromDash($string) {
        return ucfirst(str_replace('-', ' ', $string));
    }

    public static function fromUnderscore($string) {
        return ucfirst(str_replace('_', ' ', $string));
    }
}