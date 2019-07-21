<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 01/07/2015
 * Time: 09:34
 */

set_time_limit(0);

fwrite(STDOUT, "Please enter your name ");

$name = fgets(STDIN);

fwrite(STDOUT, "Hello " . $name);

exit(0);