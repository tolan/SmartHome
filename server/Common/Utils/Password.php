<?php

namespace SmartHome\Common\Utils;

class Password {

    static public function encrypt ($string) {
        return 'v1_'.password_hash($string, PASSWORD_BCRYPT);
    }

    static public function verify ($password, $hash) {
        return password_verify($password, ltrim($hash, 'v1_'));
    }

}
