<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace controllers;

use cfg\app\Controller;
use cfg\app\Reverter;
use models\Campaign;

/**
 * Description of defaultController
 *
 * @author Kiala
 */
class defaultController extends Controller
{

    public function mainAction()
    {

        return $this->render($this->pView("index.html.twig"));
    }

    public function aboutAction()
    {

        return $this->response->render("about.html.twig");
    }

    public function helpAction()
    {

        return $this->response->render("help.html.twig");
    }

    public function logoutAction()
    {

        $url = $this->generateURL('main_home');

        $this->logout();

        $this->response->redirect($url);
    }

}
