<?php

namespace cfg\app;

use cfg\app\observers\Firewall;

class Router {
    /*
     * $uri the URL string
     * $controller the controller
     * $action the action called by the user
     * $params an array of parameters needed by that action
     * $ctrl_dir the controller directory
     * $default_class the default class it no method called
     * $default_action the default action to be called
     * $default_dir the default directory of the application (root)
     * $security a security instance for allowing access or not
     */

    private static $uri;
    private static $controller;
    private static $action;
    private static $params = array();
    private static $ctrl_dir;
    private static $default_class;
    private static $default_action;
    private static $default_dir;
    private static $security;

    /**
     * @param $route_name
     * @param null $options
     * @param string $default_separator
     * @param bool $complete_path
     * @return string|void
     * @throws \Exception
     */
    public static function generateURL($route_name, $options = null, $default_separator = "_", $complete_path = null) {

        $application = new Application();

        $json_file = file_get_contents(Application::$system_files->getModulesFile());

        $json_file_content = json_decode($json_file, 1);

        $route_name_exploded = explode($default_separator, $route_name);

        $final_route_name = substr($route_name, strlen($route_name_exploded[0]) + 1);

        if (isset($json_file_content[$route_name_exploded[0]])) {

            if (isset($json_file_content[$route_name_exploded[0]]["file"])) {

                $route_filename = $json_file_content[$route_name_exploded[0]]["file"];

                if (!file_exists(Application::$system_files->getRoutingDirectory() . "/" . $route_filename)) {

                    throw new \RuntimeException("Cannot open the filename <strong>" . $route_filename . "</strong>");
                }

                $file_content = json_decode(file_get_contents(Application::$system_files->getRoutingDirectory() . "/" . $route_filename), 1);

                $route_list = $file_content['routes'];

            } else {

                $route_list = $json_file_content[$route_name_exploded[0]]['routes'];
            }

            if (isset($route_name, $route_list)) {

                /*
                 * if the filter was activated, router will check for access restriction
                 * to be sure that the client cans access to that resource
                 */
                if (Application::isFirewallEnabled()) {

                    $firewall = new Firewall($route_name);

                    if (!$firewall->doFilter()) {

                        throw new \RuntimeException("<h3>Cannot resolve URL</h3>");
                    }
                }

                self::createURL($application, $route_name_exploded[0], $route_list[$final_route_name], $options, $complete_path);

            } else {

                throw new \RuntimeException("Impossible de trouver la route {$route_name}");
            }

        } else {

            throw new \Exception("Le prefixe {$route_name_exploded[0]} n'existe dans");
        }
    }

    private static function createURL(Application $application, $parent, $route, $options, $complete_path = false) {

        if (isset($route['args'])) {

            $arguments = explode("/", $route['url']);

            array_shift($arguments);

            $argument_number = count($arguments) - 1;

            if ($arguments[$argument_number] == "") {

                array_pop($arguments);
            }

            if (count($route['args']) == count($options)) {

                $option_keys = array_keys($options);

                for ($i = 0, $c = count($route['args']); $i < $c; $i ++) {

                    $key = $option_keys[$i];
                    $arg = $route['args'][$key];

                    if (array_key_exists($key, $route['args']) && self::reGex($arg, $options[$key])) {

                        $id = array_search($key, $arguments);
                        $arguments[$id] = $options[$option_keys[$i]];

                    } else {

                        $arguments = array();
                    }
                }

                if (!$arguments) {

                    return null;

                } else {

                    $url = $parent . '/' . substr($route['url'], 0, strpos($route['url'], '/') + 1) . implode('/', $arguments);

                    return ($complete_path) ? $complete_path . $application->getSessionDefDir() . $url : $application->getSessionDefDir() . $url;
                }
            }

        } else {

            return ($complete_path) ? $complete_path . $application->getSessionDefDir() . $parent . '/' . $route['url'] : $application->getSessionDefDir() . $parent . '/' . $route['url'];
        }

        return null;
    }

    private static function reGex($pattern, $subject) {

        return preg_match("`^" . $pattern . "$`", $subject);
    }

    public static function analyse() {

        $url = explode("/", self::$uri);

        if (strstr(self::$uri, self::$default_dir)) {

            array_shift($url);
            array_shift($url);
        }

        $number = count($url) - 1;

        if (count($url) < 2) {

            try {

                self::compose(self::$default_class, self::$default_action, "");

            } catch (\RuntimeException $e) {

                die($e->getMessage());

            } catch (\Exception $e) {

                die($e);
            }

        } elseif (count($url) == 2 && $url[$number] != "") {

            self::$controller = $url[0];
            self::$action = $url[1];

            try {

                self::compose(self::$controller, self::$action, "");

            } catch (\RuntimeException $e) {

                die($e->getMessage());

            } catch (\Exception $e) {

                die($e);
            }

        } else {

            if ($url[$number] == "") {

                array_pop($url);
            }

            self::$controller = $url[0];

            if (!isset($url[1])) {

                try {

                    self::compose(self::$default_class, self::$default_action, null);

                } catch (\RuntimeException $e) {

                    die($e->getMessage());
                }
            }

            self::$action = $url[1];

            array_shift($url);
            array_shift($url);

            self::$params = implode('/', $url);

            try {

                self::compose(self::$controller, self::$action, self::$params);

            } catch (\RuntimeException $e) {

                die($e->getMessage());

            } catch (\Exception $e) {

                die($e->getMessage());
            }
        }
    }

    private static function compose($controller, $action, $params) {

        //devient true si on passe la valeur par défaut
        $default = false;

        if ($action == "") {

            throw new \Exception("This URL is not correct");
        }

        try {

            Application::$request_log->setMessage("Scan du controlleur \"{$controller}\".")->notify();
            $_controller = self::$security->scan($controller);

        } catch (\RuntimeException $ex) {

            Application::$request_log->setMessage("Erreur lors du can du controlleur \"{$controller}\".")->setType(observers\LogHandler::TYPE_ERROR)->notify();
            die($ex->getMessage());

        } catch (\Exception $ex) {

            Application::$request_log->setMessage("Erreur lors du can du controlleur \"{$controller}\".")->setType(observers\LogHandler::TYPE_ERROR)->notify();
            die($ex->getMessage());
        }

        if ($_controller) {
            
            $class = self::$ctrl_dir . $_controller . 'Controller';
            $_action = $action . 'Action';
            
        } else {

            Application::$request_log->setMessage("Génération de la route par défaut car la session avait expirée.")->notify();
            $class = self::$ctrl_dir . self::$security->scan(self::$default_class) . 'Controller';
            $_action = self::$default_action . 'Action';
            //devient true
            $default = true;
        }

        // si la classe existe
        if (class_exists($class)) {

            $class_ctrl = new \ReflectionClass($class);
            
        } else {

            throw new \RuntimeException("Le controlleur {$class} n'existe pas");
        }

        //si la classe a cette méthode
        if ($class_ctrl->hasMethod($_action)) {

            $newClass = new \ReflectionMethod($class, $_action);

            $param_count = $newClass->getNumberOfParameters();

            $arguments = $newClass->getParameters();

            if ($params && $param_count) {

                $compose_param = explode('/', $params);

                if ($param_count > 0 && $param_count == count($compose_param)) {

                    echo $newClass->invokeArgs(new $class, $compose_param);

                } elseif ($param_count > count($compose_param)) {

                    throw new \RuntimeException("La méthode demande {$param_count} parametre(s) : " . implode(', ', $arguments));
                    
                } elseif ($param_count == 0 && count($compose_param) > 0) {

                    throw new \RuntimeException("La méthode démandée n'a besoin d'aucun paramètre pour fonctionner");
                    
                } else {

                    throw new \RuntimeException("Vous n'avez pas respecté le nombre de paramètres demandé par cette fonction");
                }

            } else {

                if (!$params && $param_count) {

                    throw new \RuntimeException("La méthode demande {$param_count} parametre(s) : " . implode(', ', $arguments));
                    
                } elseif($default) {

                    echo $newClass->invoke(new $class);

                } elseif ($params && !$param_count) {

                    throw new \RuntimeException("La méthode {$action} du controleur {$controller} ne demande aucun parametre : " . $params . " : " . $param_count);
                    
                } else {

                    echo $newClass->invoke(new $class);
                }
            }

        } else {

            throw new \RuntimeException("La méthode {$_action} n'existe pas.");
        }
    }

    /**
     * retourne le nom du répertoire parent
     * 
     * @return string
     */
    public function defDir() {

        return self::$default_dir;
    }

    /**
     * Cette méthode appelle deux méthodes et renvoie la vue
     * 
     * @param string $route_name
     * @param array $options
     */
    public static function view($route_name, $options = null) {

        try {

            Application::$request_log->setMessage("Génération de lURL " . $route_name)->notify();

            self::$uri = self::generateURL($route_name, $options);

            self::analyse();

        } catch (\RuntimeException $ex) {

            die($ex->getMessage());
        }
    }

    public function __construct($URI, $ctrl_dir, $default_class, $default_action, $default_dir) {

        self::$uri = $URI;
        self::$ctrl_dir = $ctrl_dir;
        self::$default_action = $default_action;
        self::$default_class = $default_class;
        self::$default_dir = $default_dir;
        self::$security = new Security();

        $this->analyse();
    }

}
