<?php

namespace tdt4237\webapp;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;

class Auth
{
    function __construct()
    {
    }

    static function checkCredentials($username, $password)
    {
        $user = User::findByUser($username);

        if ($user === null) {
            return false;
        }

        return Hash::check($password, $user->getPasswordHash());
    }

    /**
     * Check if is logged in.
     */
    static function check()
    {
        return isset($_SESSION['user']);
    }

    /**
     * Check if the person is a guest.
     */
    static function guest()
    {
        return self::check() === false;
    }

    /**
     * Get currently logged in user.
     */
    static function user()
    {
        if (self::check()) {
            return User::findByUser($_SESSION['user']);
        }

        throw new \Exception('Not logged in but called Auth::user() anyway');
    }

    /**
     * Is currently logged in user admin?
     */
    static function isAdmin()
    {
        if (self::check() && isset($_SESSION['isAdmin'])) {
            return $_SESSION['isAdmin'];
        } else {
			return false;
		}
    }

	/**
	 * Generates a random number, stores it in session and returns it.
	 *
	 * Random number can be used in forms to prevent CSRF attacks.
	 */
	static function getNonce()
	{
		$retval = '';
		for ($i = 0; $i < 256; $i++) {
			$retval .= chr(mt_rand(0, 255));
		}
		$retval = hash('sha256', $retval);
		
		$_SESSION['nonce'] = $retval;

		return $retval;
	}

	/**
	 * Checks whether given nonce is currently valid. Returns boolean indicating validity.
	 */
	static function checkNonce($nonce)
	{
		if (!isset($_SESSION['nonce']))
			return false;
		return ($nonce == $_SESSION['nonce']);
	}

    static function logout()
    {
        session_destroy();
    }
}
