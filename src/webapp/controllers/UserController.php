<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\models\User;
use tdt4237\webapp\Hash;
use tdt4237\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::guest()) {
            $this->render('newUserForm.twig', []);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function create()
    {
        $request = $this->app->request;
        $username = $request->post('user');
        $pass = $request->post('pass');
        $email = $request->post('email');
        $question = $request->post('question');
        $answer = $request->post('answer');
		$nonce = $request->post('nonce');

		if (!Auth::checkNonce($nonce)) {
			$this->app->flashNow('error', 'Broken session.');
			$this->render('newUserForm.twig', ['username' => $username]);
			return;
		}

        $hashed = Hash::make($pass);

		$validationErrors = [];

        $user = User::makeEmpty();
        $user->setUsername($username);
        $user->setHash($hashed);
		$user->setEmail($email);
		if ($question && trim($question) != "") {
			$user->setVQuestion($question);
		} else {
			$validationErrors[] = 'You have to set a security question that could be used for password recovery.';
		}
		if ($answer && trim($answer) != "") {
			$user->setVAnswer($answer);
		} else {
			$validationErrors[] = 'You have to set an answer to your security question.';
		}

        $validationErrors = array_merge($validationErrors, User::validate($user, $email, $pass));

        if (sizeof($validationErrors) > 0) {
            $errors = join("<br>\n", $validationErrors);
            $this->app->flashNow('error', $errors);
            $this->render('newUserForm.twig', [
				'username' => $username,
				'email' => $email,
				'question' => $question
			]);
		} else {
            $user->save();
            $this->app->flash('info', 'Thanks for creating a user. Now log in.');
            $this->app->redirect('/login');
        }
    }

    function all()
    {
        $users = User::all();
        $this->render('users.twig', ['users' => $users]);
    }

    function logout()
    {
		$nonce = $this->app->request->get('nonce');
		
		if (Auth::checkNonce($nonce)) {	
			Auth::logout();
			$this->app->redirect('/?msg=Successfully logged out.');
		} else {
			$this->app->flash('info', 'Broken session.');
			$this->app->redirect('/');
		}
    }

    function show($username)
    {
        $user = User::findByUser($username);

        $this->render('showuser.twig', [
            'user' => $user,
            'username' => $username
        ]);
    }

    function edit()
    {
        if (Auth::guest()) {
            $this->app->flash('info', 'You must be logged in to edit your profile.');
            $this->app->redirect('/login');
            return;
        }

        $user = Auth::user();

        if (! $user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        }

        if ($this->app->request->isPost()) {
            $request = $this->app->request;
            $email = $request->post('email');
            $bio = $request->post('bio');
            $age = $request->post('age');
			$nonce = $request->post('nonce');

			if (!Auth::checkNonce($nonce)) {
                $this->app->flashNow('error', 'Broken session.');
        		$this->render('edituser.twig', ['user' => $user]);
				return;
			}

            $user->setEmail($email);
            $user->setBio($bio);
            $user->setAge($age);

            $pictureOk = true;
            if($_FILES["profile"]["name"] != null){

                if($_FILES["profile"]["size"] > 100000){
                    $this->app->flashNow('error', 'Profile picture size is too big, max 100KB');
                    $pictureOk = false;
                }

                $result_array = getimagesize($_FILES['profile']['tmp_name']);
                if ($result_array == null || false === $ext = array_search($result_array['mime'],
                        array(
                            'jpg' => 'image/jpeg',
                            'png' => 'image/png',
                            'gif' => 'image/gif',
                        ), true)) {
                    $this->app->flashNow('error', 'Profile picture must be jpg, png or gif');
                    $pictureOk = false;
                }

                if ($pictureOk === true) {
                    $target_dir = "uploads/";
                    $fileName = sha1_file($_FILES['profile']['tmp_name']). "." . $ext;
                    $target_dir = $target_dir . $fileName;

                    if(move_uploaded_file($_FILES['profile']['tmp_name'], $target_dir)){
                        $user->setProfilePicPath($target_dir);
                    }else{
                        $pictureOk = false;
                        $this->app->flashNow('error', 'There was an error uploading the profile picture');
                    }
                }
            }
            else{
                $user->setProfilePicPath("");
            }

            if (! User::validateAge($user)) {
                $this->app->flashNow('error', 'Age must be between 0 and 150.');
            } else if($pictureOk === true) {
                $user->save();
                $this->app->flashNow('info', 'Your profile was successfully saved.');
            }
        }

        $this->render('edituser.twig', ['user' => $user]);
    }
}
