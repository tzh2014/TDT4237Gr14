<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
            $this->render('login.twig', []);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $user = $request->post('user');
        $pass = $request->post('pass');
		$nonce = $request->post('nonce');

		if (!Auth::checkNonce($nonce)) {
			$this->app->flashNow('error', "Broken session.");
			$this->render('login.twig', []);
		} else if (Auth::checkCredentials($user, $pass)) {
            session_regenerate_id();
            $_SESSION['user'] = $user;
			$_SESSION['isAdmin'] = User::findByUser($user)->isAdmin();

            $this->app->flash('info', "You are now successfully logged in as $user.");
            $this->app->redirect('/');
        } else {
            $this->app->flashNow('error', 'Incorrect user/pass combination.');
            $this->render('login.twig', []);
        }
    }
}
