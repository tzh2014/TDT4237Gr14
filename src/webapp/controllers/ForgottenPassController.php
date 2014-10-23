<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class ForgottenPassController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
		$this->render('forgottenPass.twig', []);
    }

	function sendEmail()
	{
		$this->render('showEmail.twig', []);
	}

	function verification($hash)
	{
		$this->render('forgottenPass-verify.twig', [ 'hash' => $hash ]);
	}

	function setNewPass($hash)
	{
		$this->render('forgottenPass-new.twig', []);
	}

	function changePass()
	{
		$this->app->flash('info', "Now you can log in with your new password.");
		$this->app->redirect('/login');
	}
}

