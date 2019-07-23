<?php
/**
 * Created by PhpStorm.
 * User: KIALA
 * Date: 11/05/2016
 * Time: 12:07
 */

namespace cfg\app;

class SecurityV2
{
    private $module;
    private $controller;
    private $roles;
    private static $application = null;

    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public static function checkSessionExists()
    {

        return self::$application->getSession()->get('usr_auth');
    }


    public static function checkApplicationRole()
    {
        if (self::$application->getRoleType() == Application::ROLE_DB) {

            return true;
        }

        return false;
    }


    public function __construct()
    {
        if (is_null(self::$application)) {

            self::$application = new Application();
        }
    }
}