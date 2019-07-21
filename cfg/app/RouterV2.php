<?php
/**
 * Created by PhpStorm.
 * User: KIALA
 * Date: 11/05/2016
 * Time: 11:36
 */

namespace cfg\app;


use cfg\app\observers\Firewall;

class RouterV2
{
    private static $uri;
    private static $ctrl_dir;
    private static $default_controller;
    private static $default_action;
    private static $default_dir;
    private static $security;

    public static function generateURL($route_name, $options = null, $default_separator = "_", $complete_path = null)
    {

        // create application instance
        $application = new Application();
        // getting modules file on system files
        $json_file = file_get_contents(Application::$system_files->getModulesFile());
        $json_file_content = json_decode($json_file, 1);
        $route_name_exploded = explode($default_separator, $route_name);
        $final_route_name = substr($route_name, strlen($route_name_exploded[0]) + 1);

        if (isset($json_file_content[$route_name_exploded[0]])) {

            // the file prefix has a file with the same name
            // trying to get it
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

                return self::createURL($application, $route_name_exploded[0], $route_list[$final_route_name], $options, $complete_path);
            }

            throw new \RuntimeException("Impossible de trouver la route {$route_name}");

        } else {

            throw new \Exception("Le prefixe {$route_name_exploded[0]} n'existe dans");
        }
    }

    /**
     * @param Application $application
     * @param $parent
     * @param $route
     * @param $options
     * @param bool $complete_path
     * @return bool|string
     */
    private static function createURL(Application $application, $parent, $route, $options, $complete_path = false)
    {

        $motif = preg_match_all("#:[a-z0-a]+#", $route["url"], $results);
        $final_url = $route['url'];

        if ($motif && isset($route['args'])) {

            $result_count = count($results[0]);
            $argument_count = count($route['args']);
            $option_count = count($options);

            if ($result_count && ($result_count == $argument_count && $argument_count == $option_count)) {

                $all_keys_exists = false;

                $route_arguments = $route['args'];

                foreach ($results[0] as $result) {

                    $result_str = substr($result, 1);

                    if (array_key_exists($result_str, $options) && array_key_exists($result_str, $route_arguments)) {

                        if (self::reGex($route_arguments[$result_str], $options[$result_str])) {

                            $all_keys_exists = true;

                            $final_url = str_replace($result, $options[$result_str], $final_url);

                        }

                    } else {

                        $all_keys_exists = false;
                    }
                }

                if (!$all_keys_exists) return false;

                $final_url = $parent . '/' . $final_url;
            }

        }

        return ($complete_path) ? $complete_path . $application->getSessionDefDir() . $final_url : $application->getSessionDefDir() . $final_url;
    }

    private static function reGex($pattern, $subject)
    {

        return preg_match("`^" . $pattern . "$`", $subject);
    }

    public static function analysis()
    {
        $http_url = explode("/", self::$uri);

        $log = Application::$request_log;

        if (strstr(self::$uri, self::$default_dir)) {

            //shift 2x to got the final url
            array_shift($http_url);
            array_shift($http_url);

            // in production stage, shift once again
            if (Application::getStage() == Application::STAGE_PRODUCTION) {

                array_shift($http_url);
            }

            $json_file = file_get_contents(Application::$system_files->getModulesFile());
            $json_file_content = json_decode($json_file, 1);

            $url_size = count($http_url);

            if ($url_size < 2) {

                if ($http_url[0] == "") {

                    self::compose(self::$default_controller, self::$default_action, array());

                } else {

                    goto FLAG;
                }

            } else {

                FLAG:

                $route_name = $http_url[0];
                $url_pattern = substr(self::$uri, strpos(self::$uri, $route_name));
                $url_pattern = substr($url_pattern, strlen($route_name) + 1);

                if (!$url_pattern) $url_pattern = "/";

                $log->setMessage(sprintf("The url pattern is %s, while the route name is %s", $url_pattern, $route_name))->notify();

                if (isset($json_file_content[$route_name])) {

                    $log->setMessage(sprintf("The route %s exists", $route_name))->notify();

                    if (isset($json_file_content[$route_name]["file"])) {

                        $log->setMessage(sprintf("The file key exists for %s", $route_name))->notify();

                        $route_filename = $json_file_content[$route_name]["file"];
                        $route_file_location = Application::$system_files->getRoutingDirectory() . "/" . $route_filename;

                        if (!file_exists($route_file_location)) {

                            throw new \RuntimeException("Cannot open the filename <strong>" . $route_filename . "</strong>");
                        }

                        $log->setMessage(sprintf("The file exists for %s route", $route_name))->notify();

                        $route_file_content = file_get_contents($route_file_location);

                        $log->setMessage(sprintf("The type of that file is %s", gettype($route_file_content)))->notify();

                        $route_content_array = json_decode($route_file_content, 1);

                        if (isset($route_content_array["session"])) {

                            $no_problem = true;

                            if ($route_content_array["session"]) {

                                $log->setMessage(sprintf("The session key exists"))->notify();

                                if (!SecurityV2::checkSessionExists()) {

                                    $no_problem = false;
                                }
                            }

                            if ($no_problem) {

                                if (isset($route_content_array["controller"])) {

                                    $controller = $route_content_array["controller"];

                                    $log->setMessage(sprintf("The controller for %s route is %s", $route_name, $controller))->notify();

                                    if (isset($route_content_array["routes"])) {

                                        $log->setMessage(sprintf("The routes key exists"))->notify();

                                        $routes_list = $route_content_array["routes"];

                                        $route_not_exists = false;

                                        foreach ($routes_list as $item) {

                                            if (isset($item["url"])) {

                                                $local_url = $item["url"];

                                                preg_match_all("#:[a-z0-a]+#", $local_url, $results);

                                                if ($results[0]) {

                                                    $actual_url_pattern_exploded = explode("/", $local_url);

                                                    if (sizeof($actual_url_pattern_exploded) == $url_size - 1) {

                                                        $compare_url = substr($local_url, 0, strpos($local_url, "/:"));

                                                        if (strstr($url_pattern, $compare_url)) {

                                                            $log->setMessage(sprintf("**The corresponding route is %s, the non variable is %s", $local_url, $compare_url))->notify();

                                                            $request_parameters = explode("/", substr($url_pattern, strlen($compare_url) + 1));

                                                            if (isset($item["args"])) {

                                                                $log->setMessage(sprintf("Arguments detected!"))->notify();

                                                                $local_args = $item["args"];

                                                                if (count($results[0]) == count($request_parameters)) {

                                                                    $log->setMessage(sprintf("The arguments result is equals to %d", count($local_args)))->notify();

                                                                    $i = 0;
                                                                    $all_parameters_exists = count($request_parameters);

                                                                    foreach ($results[0] as $result) {

                                                                        $pattern = substr($result, 1);

                                                                        if (isset($local_args[$pattern])) {

                                                                            $log->setMessage(sprintf("The pattern %s exists", $pattern))->notify();

                                                                            if (self::reGex($local_args[$pattern], $request_parameters[$i])) {

                                                                                $all_parameters_exists--;

                                                                            }
                                                                        }

                                                                        $i++;
                                                                    }

                                                                    if ($all_parameters_exists == 0) {

                                                                        $log->setMessage(sprintf("All parameters are OK"))->notify();

                                                                        if (isset($item["action"])) {

                                                                            $log->setMessage(sprintf("The action key exists. The controller is %s and the action is %s", $controller, $item["action"]))->notify();

                                                                            try {

                                                                                self::compose($controller, $item["action"], $request_parameters);

                                                                                $route_not_exists = false;

                                                                                break;

                                                                            } catch (\Exception $e) {

                                                                                die($e->getMessage());
                                                                            }

                                                                        }
                                                                    }
                                                                }

                                                            }
                                                        }
                                                    }

                                                } else {

                                                    if ($local_url == $url_pattern) {

                                                        $log->setMessage(sprintf("**The corresponding route is %s, the non variable is %s", $local_url, $url_pattern))->notify();

                                                        if (isset($item["action"])) {

                                                            $log->setMessage(sprintf("The action key exists. The controller is %s and the action is %s", $controller, $item["action"]))->notify();

                                                            try {

                                                                self::compose($controller, $item["action"], array());

                                                            } catch (\Exception $e) {

                                                                die($e->getMessage());
                                                            }

                                                            $route_not_exists = false;

                                                            break;

                                                        } else {

                                                            $text = sprintf("No action key for the action %s", $url_pattern);

                                                            $log->setMessage($text)->notify();

                                                            echo $text;
                                                        }

                                                    } else {

                                                        $route_not_exists = true;
                                                    }
                                                }
                                            }
                                        }

                                        if ($route_not_exists) {

                                            printf("La route %s n'existe pas !", $url_pattern);
                                        }
                                    }
                                }
                            }
                        }

                    }
                } else {

                    printf("Cette page n'existe pas !");
                }
            }
        }
    }

    private static function compose($controller, $action, $parameters)
    {

        $class = self::$ctrl_dir . $controller . 'Controller';
        $_action = $action . 'Action';

        // si la classe existe
        if (class_exists($class)) {

            $class_ctrl = new \ReflectionClass($class);

        } else {

            throw new \RuntimeException("Le controlleur {$class} n'existe pas");
        }

        //if the class has that method
        if ($class_ctrl->hasMethod($_action)) {

            $reflectionMethod = new \ReflectionMethod($class, $_action);

            $param_count = $reflectionMethod->getNumberOfParameters();

            $arguments = $reflectionMethod->getParameters();

            if ($parameters && $param_count) {

                if ($param_count > 0 && $param_count == count($parameters)) {

                    echo $reflectionMethod->invokeArgs(new $class, $parameters);

                } elseif ($param_count > count($parameters)) {

                    throw new \RuntimeException("La méthode demande {$param_count} parametre(s) : " . implode(', ', $arguments));

                } elseif ($param_count == 0 && count($parameters) > 0) {

                    throw new \RuntimeException("La méthode démandée n'a besoin d'aucun paramètre pour fonctionner");

                } else {

                    throw new \RuntimeException("Vous n'avez pas respecté le nombre de paramètres demandé par cette fonction");
                }

            } else {

                if (!$parameters && $param_count) {

                    throw new \RuntimeException("La méthode demande {$param_count} parametre(s) : " . implode(', ', $arguments));

                } elseif (!$parameters && !$param_count) {

                    echo $reflectionMethod->invoke(new $class);

                } elseif ($parameters && !$param_count) {

                    throw new \RuntimeException("La méthode {$action} du controleur {$controller} ne demande aucun parametre : " . $parameters . " : " . $param_count);

                } else {

                    echo $reflectionMethod->invoke(new $class);
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
    public function defDir()
    {

        return self::$default_dir;
    }

    /**
     * Cette méthode appelle deux méthodes et renvoie la vue
     *
     * @param string $route_name
     * @param array $options
     */
    public static function view($route_name, $options = null)
    {

        try {

            Application::$request_log->setMessage("Génération de lURL " . $route_name)->notify();

            self::$uri = self::generateURL($route_name, $options);

            self::analysis();

        } catch (\RuntimeException $ex) {

            die($ex->getMessage());
        }
    }

    public function __construct($URI, $ctrl_dir, $default_controller, $default_action, $default_dir)
    {

        self::$uri = $URI;
        self::$ctrl_dir = $ctrl_dir;
        self::$default_action = $default_action;
        self::$default_controller = $default_controller;
        self::$default_dir = $default_dir;
        self::$security = new SecurityV2();

        $this->analysis();
    }
}