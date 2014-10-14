<?php

namespace tdt4237\webapp;

class Hash
{
    function __construct()
    {
    }

    static function make($plaintext)
    {
        return hash('sha512', $plaintext);
    }

    static function check($plaintext, $hash)
    {
        return self::make($plaintext) === $hash;
    }
}
