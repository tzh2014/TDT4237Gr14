<?php

namespace tdt4237\webapp;

class Hash
{
    function __construct()
    {
    }

    static function make($plaintext)
    {
        return password_hash($plaintext, PASSWORD_BCRYPT);
    }

    static function check($plaintext, $hash)
    {
        return password_verify($plaintext, $hash);
    }
}
