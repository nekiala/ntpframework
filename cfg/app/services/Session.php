<?php

namespace cfg\app\services;
use cfg\app\Application;
use models\User;

/**
 * Cette classe contient tous les paramètres pour maninuper les sessions
 *
 * @author Kiala
 */
class Session {

    const ERASE_MODE = 1;
    const NO_ERASE = 2;

    /**
     * @return string
     */
    public function getFlash()
    {
        $flash = $this->get("flash");
        $this->remove("flash");

        return $flash;
    }

    public function hasFlash() {

        return $this->get("flash");
    }

    /**
     * @param string $flash
     */
    public function setFlash($flash)
    {
        $this->save("flash", $flash);
    }

    /**
     * 
     * @param string $cle
     * @return string
     */
    public function get($cle) {
        if (isset($_SESSION[self::getAppName()][$cle]) && !empty($_SESSION[self::getAppName()][$cle])) {
            return $_SESSION[self::getAppName()][$cle];
        } else {
            return FALSE;
        }
    }

    public function createToken() {
        return md5(uniqid(rand(), true));
    }

    public function saveToken($value) {
        $this->save(Application::TOKEN_NAME, $value);
    }

    public function saveFormToken($value) {

        $this->save(Application::FORM_TOKEN_NAME, $value);
    }

    public function getToken($name = "token") {

        return $this->get($name);
    }

    public function getFormToken() {

        return $this->get(Application::FORM_TOKEN_NAME);
    }

    public static function getAppName()
    {
        $application = json_decode(file_get_contents(Application::$system_files->getApplicationFile()), 1);
        $config_section = $application["application_cfg"];
        $app_name = isset($config_section["root"]) ? str_replace("/", "", $config_section["root"]) : str_shuffle(time());


        return $app_name;
    }

    public function start() {

        if (!isset($_SESSION[self::getAppName()])) {
            session_start();
            return $this;
        }
    }

    public function destroy() {

        $this->start();
        $_SESSION[self::getAppName()] = array();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 4200, $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public function remove($key) {

        if ($cle = $this->get($key)) {

            unset($_SESSION[self::getAppName()][$key]);
        }
    }

    public function save($key, $value, $mode = self::ERASE_MODE) {

        if ($mode == self::ERASE_MODE) {
            $_SESSION[self::getAppName()][$key] = $value;
        } else {
            $_SESSION[self::getAppName()][$key] .= $value;
        }
    }

    public function decode() {

        $detail = unserialize($this->get('usr_info'));
        return $detail;
    }

    public function username() {

        $detail = $this->decode();
        return $detail->getLogin();
    }

    public function system() {
        $detail = $this->decode();
        return $detail->getSystem();
    }

    public function role() {

        $role = unserialize($this->get('usr_roles'));
        return ($role) ? $role : "--";
    }

    public function preference() {

        $user = $this->decode();

        return $user->getSystem()->getShortName();
    }

    public function usr() {
        $detail = $this->decode();
        return $detail->getId();
    }
    
    public function getUser() {
        
        return $this->decode();
    }

    public function pwd() {

        $detail = $this->decode();
        return $detail->getPassword();
    }

    public function saveUser(User $user) {
        $this->save('usr_info', serialize($user));
    }

    public function __construct() {
        // yantika session muna kundulu
        $this->start();
    }

}
