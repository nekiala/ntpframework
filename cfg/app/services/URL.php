<?php
/**
 * Created by PhpStorm.
 * User: Kiala
 * Date: 09/06/2015
 * Time: 22:24
 */

namespace cfg\app\services;


use cfg\app\Application;

class URL {

    private $application = null;

    public function transformToStr($int) {

        $out = $int + date("Y") . "." . substr(sha1(str_shuffle("ntoprog")), 0, 5);
        $number = $int + 12;

        return base64_encode($out . "." . $number);
    }

    public function t($int)
    {
        return $this->transformToStr($int);
    }

    public function transformToNumber($str) {

        $number = explode(".", base64_decode($str));

        return $number[0] - date("Y");
    }

    public function encodeUrl($parameter)
    {
        return base64_encode($parameter);
    }

    public function decodeUrl($parameter)
    {
        return base64_decode($parameter);
    }

    public function __construct()
    {
        $this->application = new Application();
    }

    /**
     * this method checks that the user is authorized to display that page
     * @param $page_name
     * @return bool
     */
    public function check($page_name)
    {
        $urls = unserialize($this->application->getSession()->get("urls"));

        return in_array($page_name, $urls);
    }
}