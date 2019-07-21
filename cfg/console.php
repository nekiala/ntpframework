<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include 'app/autoload.func.php';

use cfg\app\db\Connector;

$arguments = $argv;

$connector = new Connector(false);

array_shift($arguments);

