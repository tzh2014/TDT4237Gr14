<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\Hash;
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
		$request = $this->app->request;
		$username = $request->post('user');
		$nonce = $request->post('nonce');
		
		if (!Auth::checkNonce($nonce)) {
			$this->app->flash('error', "Broken session.");
			$this->app->redirect('/forgot');
		}

        $user = User::findByUser($username);
		if ($user == null) {
			$this->app->flash('error', "No user with username '$username' was found.");
			$this->app->redirect('/forgot');
		}

		$passResetHash = '';
		for ($i = 0; $i < 256; $i++) {
			$passResetHash .= chr(mt_rand(0, 255));
		}
		$passResetHash = hash('sha256', $passResetHash);
		$this->app->db->query("UPDATE users SET passReset = '$passResetHash' WHERE id = '" . $user->getId() . "'");

		$serverRoot = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

		$to = $user->getEmail();
		$subject = "Password reset request from Movie Reviews app";
		$message = "To reset your password please visit the following link:
<br /><br />
	$serverRoot/forgot/$passResetHash
<br /><br />
If you did not ask for password reset, please ignore this email.";

		$this->render('showEmail.twig', ['to' => $to, 'subject' => $subject, 'message' => $message]);
	}

	function verification($hash)
	{
		$user = User::findByResetHash($hash);
		if ($user == null) {
			$this->app->notFound();
		}

		$this->render('forgottenPass-verify.twig', [ 
			'username' => $user->getUserName(),
			'question' => $user->getVQuestion()
		]);
	}

	function setNewPass($hash)
	{
		$user = User::findByResetHash($hash);
		if ($user == null) {
			$this->app->notFound();
		}
		
		$request = $this->app->request;
		$answer = $request->post('ans');
		$nonce = $request->post('nonce');
		
		if (!Auth::checkNonce($nonce)) {
			$this->app->flashNow('error', "Broken session.");
			$this->render('forgottenPass-verify.twig', [ 
				'username' => $user->getUserName(),
				'question' => $user->getVQuestion()
			]);
			return;
		}

		if (! password_verify($answer, $user->getVAnswer())) {
			$this->app->flashNow('error', 'Your answer was wrong.');
			$this->render('forgottenPass-verify.twig', [ 
				'username' => $user->getUserName(),
				'question' => $user->getVQuestion()
			]);
			return;
		}
		$_SESSION['passChgUsrID'] = $user->getId();

		$this->render('forgottenPass-new.twig', [
			'username' => $user->getUserName()
		]);
	}

	function changePass()
	{
		$request = $this->app->request;
		$pass = $request->post('pass');
		$nonce = $request->post('nonce');
		
		if (!Auth::checkNonce($nonce) || !isset($_SESSION['passChgUsrID'])) {
			$this->app->flash('error',
				"Broken session. Password resetting failed. Please try again and don't use back button in your browser.");
			$this->app->redirect('/');
			return;
		}

        $validationErrors = User::validatePassword($pass);

        if (sizeof($validationErrors) > 0) {
            $errors = join("<br>\n", $validationErrors);
			$this->app->flashNow('error', $errors);
			$this->render('forgottenPass-new.twig', []);
			return;
        }

		User::setNewPassword($_SESSION['passChgUsrID'], Hash::make($pass));
		$this->app->db->query("UPDATE users SET passReset = NULL WHERE id = '" . $_SESSION['passChgUsrID'] . "'");

		$this->app->flash('info', "Now you can log in with your new password.");
		$this->app->redirect('/login');
	}
}

