<?php
/**
 * Created by PhpStorm.
 * User: KIALA
 * Date: 11/05/2016
 * Time: 12:13
 */

namespace controllers;


use cfg\app\Controller;

class agentController extends Controller
{
    public function listAction()
    {
        echo "Yenge kieno";
    }

    public function listingAction($a, $b)
    {
        printf("I got parameters %s and %s", $a, $b);
    }

    public function listedAction()
    {
        echo "Bundu dia Kongo";
    }

    public function theaterAction()
    {
        printf("This is the theater action");
    }

    public function showAction($id)
    {
        printf("The result is %d", $id);
    }

    public function editAction($id)
    {
        printf("The editing item is %d", $id);
    }
}