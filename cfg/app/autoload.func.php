<?php

function __autoload($class_name) {
    include(str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php');
}