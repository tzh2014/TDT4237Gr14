<?php

namespace tdt4237\webapp\controllers;

use tdt4237\webapp\Auth;
use tdt4237\webapp\models\User;

class AdminController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::guest()) {
            $this->app->flash('info', "You must be logged in to view the admin page.");
            $this->app->redirect('/');
        }

        if (! Auth::isAdmin()) {
            $this->app->flash('info', "You must be administrator to view the admin page.");
            $this->app->redirect('/');
        }

        $variables = [
            'users' => User::all()
        ];
        $this->render('admin.twig', $variables);
    }

    function delete($username)
    {	
		$nonce = $this->app->request->get('nonce');
		if (! Auth::checkNonce($nonce)) {
            $this->app->flash('info', "Broken session.");
            $this->app->redirect('/admin');
			return;
		}

        if (Auth::guest()) {
            $this->app->flash('info', "You must be logged in to delete the user.");
            $this->app->redirect('/');
			return;
        }    

        if (! Auth::isAdmin()) {
            $this->app->flash('info', "You must be administrator to delete the user.");
            $this->app->redirect('/');
        	return;
		}
        
        if (User::deleteByUsername($username)) {
            $this->app->flash('info', "Sucessfully deleted '$username'");
        } else {
            $this->app->flash('info', "An error ocurred. Unable to delete user '$username'.");
        }

        $this->app->redirect('/admin');
    }
}
