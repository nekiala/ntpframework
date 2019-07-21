<?php

namespace cfg\app;

/**
 * Description of Security
 *
 * @author Kiala
 */
class Security {

    //put your code here
    private $module;
    private $controller;
    private $roles;
    private static $application = null;

    public function setModule($module) {
        $this->module = $module;
        return $this;
    }

    public function getModule() {
        return $this->module;
    }

    public function setController($controller) {
        $this->controller = $controller;
        return $this;
    }

    public function getController() {
        return $this->controller;
    }

    public function setRoles(array $roles) {
        $this->roles = $roles;
    }

    public function getRoles() {
        return $this->roles;
    }

    /**
     * vérifie si l'utilisateur est connecté,
     * en regardant si la clée <b>usr_auth</b> existe
     * 
     * @return boolean
     */
    private function verifySession() {
        //vérifie si l'utilisateur est connecté
        return isset($_SESSION['usr_auth']);
    }

    public function scan($module) {

        $json = file_get_contents(Application::$system_files->getModulesFile());
        $session = self::$application->get("session");

        $user_role = $session->get('user_role');

        $filename = json_decode($json, 1);

        if (isset($filename[$module])) {

            if (isset($filename[$module]["file"])) {

                $new_filename = $filename[$module]["file"];

                $module_name = json_decode(file_get_contents(Application::$system_files->getRoutingDirectory() . "/" . $new_filename), 1);

            } else {

                $module_name = $filename[$module];
            }

            //si le module require la session
            if (isset($module_name['session']) && $module_name['session']) {

                if ($this->verifySession()) {

                    return $this->returnController($module_name, $user_role);
                    
                } else {

                    return false;
                }
            } else {
                
                return $this->returnController($module_name, $user_role);
            }
        } else {

            throw new \RuntimeException("Ce module n'existe pas.");
        }
    }

    private function returnController($module, $user_role) {

        // si la lecture des rôles se fait dans la base de données
        // alors on retourne directement le controleur
        if (self::$application->getRoleType() == Application::ROLE_DB) {

            return $module['controller'];
        }

        if (in_array("anonyme", $module['roles'])) {

            return $module['controller'];
            
        } elseif (in_array($user_role, $module['roles'])) {

            return $module['controller'];
            
        } else {

            throw new \RuntimeException("Vous n'avez pas l'accès à ce module.");
        }
    }

    public function __construct() {
        if (is_null(self::$application)) {

            self::$application = new Application();
        }
    }

}
