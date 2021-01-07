<?php

namespace SmartHome\Common\Utils;

/**
 * This file defines class for work with Password.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Password {

    /**
     * Enrypts password to hash
     *
     * @param string $string Password
     *
     * @return string
     */
    public static function encrypt($string) {
        return 'v1_'.password_hash($string, PASSWORD_BCRYPT);
    }

    /**
     * Verifies password againts to hash
     *
     * @param string $password Password
     * @param string $hash     Hash
     *
     * @return boolean
     */
    public static function verify($password, $hash) {
        return password_verify($password, ltrim($hash, 'v1_'));
    }

}
