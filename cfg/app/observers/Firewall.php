<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 31/07/2015
 * Time: 21:32
 */

namespace cfg\app\observers;


use cfg\app\Application;

class Firewall {

    private $route;
    private static $application = null;

    public function __construct($route_name) {
        $this->route = $route_name;

        Application::$request_log->setMessage("Entered to the filter object.")->notify();

        if (is_null(self::$application)) self::$application = new Application();
    }

    public function doFilter() {

        $session = self::$application->get("session");
        $pages = unserialize($session->get("pages"));

        Application::$request_log->setMessage("Preparing for do filter with route name '" . $this->route . "''")->notify();

        $status = false;

        if (!$pages) {

            return true;
        }

        foreach ($pages as $page) {

            if ($page->getUrl() == $this->route) {

                $status = true;
                break;
            }
        }

        return $status;
    }
}