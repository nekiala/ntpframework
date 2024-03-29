<?php
set_time_limit(0);
ob_implicit_flush();
ob_start("ob_gzhandler");
ini_set('display_errors', 1);
date_default_timezone_set("Africa/Kinshasa");

include 'cfg/app/autoload.func.php';

require __DIR__ . "/vendor/autoload.php";

$json = "cfg/application_config.json";
$file = json_decode(file_get_contents($json), true);

$php_version = $file['dependency']["php"];

if (!\cfg\app\Application::checkPHPVersion($php_version)) {
    die("The application works with PHP version {$php_version}. Your actual PHP version is " . PHP_VERSION);
}

$dir = substr(dirname(__FILE__), strrpos(dirname(__FILE__), DIRECTORY_SEPARATOR) + 1);

$app = new cfg\app\Application(true);
$app->setEnvironment(PHP_OS);
$app->setDefDir($app->getEnvironment(), $dir);
$app->setRoleType(\cfg\app\Application::ROLE_FILE);
$app->setFirewallEnabled(false);
$app->setUserMustBeEnabled(true);
$app->setDbServer(\cfg\app\db\DBServer::MARIADB);
$app->setStage(\cfg\app\Application::STAGE_DEVELOPMENT);
$app->run();
