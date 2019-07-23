<?php

// updated autoload for PHP 7 requirement
function ntp_loader($class_name) {
    include(str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php');
}

spl_autoload_register("ntp_loader");