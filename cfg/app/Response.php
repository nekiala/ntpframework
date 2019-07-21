<?php

namespace cfg\app;
use cfg\Translator;
use Twig_Environment;
use Twig_Loader_Filesystem;

class Response {
    
    const VIEW_PATH = "views/";
    
    private static $application;
    private static $twig = null;

    public function render($view, $params = null) {

        if (file_exists(self::VIEW_PATH . str_replace(":", "/", $view))) {
            
            $twig = new Twig_Environment(new Twig_Loader_Filesystem('views'), array('cache' => false, 'debug' => true));

            $twig->addExtension(new \Twig_Extension_Debug());
            $twig->addFunction(new \Twig_SimpleFunction('path', function($file = null) {
                if ($file == null) {
                    return self::$application->getSessionDefDir();
                }
                return self::$application->getSessionDefDir() . $file;
            }));
            $twig->addFunction(new \Twig_SimpleFunction('url', function($route = null, $options = null) {
                if ($route === null) {
                    
                    throw new \RuntimeException("Veuillez passer le nom de la route");
                }
                try {
                    return RouterV2::generateURL($route, $options);
                } catch(\Exception $e) {
                    
                    die ($e->getMessage());
                }
            }));
            $twig->addGlobal("session", self::$application->getSession());
            $twig->addGlobal("md", self::$application->get('parsedown'));
            $twig->addGlobal("tm", self::$application->get('timing'));
            $twig->addGlobal("url", self::$application->get('serv.url'));
            $twig->addGlobal("trans", new Translator());

            $twig->addFunction(new \Twig_SimpleFunction('view', function($route_name, $options = null) {
                
                Router::view($route_name, $options);
            }));

            $twig->addFunction(new \Twig_SimpleFunction("loader", function() {
                return '<span style="visibility:hidden"
                                                                                       class="loader pull-right"><img
                        src="' . self::$application->getSessionDefDir() . 'views/lib/img/loader.gif" alt="loader"/></span>';
            }));

            $twig->addFunction(new \Twig_SimpleFunction('status', function($status) {
                $st_array = array('12.png', '10.png');
                return '<img
                        src="' . self::$application->getSessionDefDir() . 'views/lib/img/icones/' . $st_array[$status] . '"/>';
            }));

            $twig->addFunction(new \Twig_SimpleFunction('change_content', function($href, $save_history = true, $span = null, $text = null) {
                return ' onclick="_system.select_type(\'' . $href . '\', ' . $save_history . '); return false';
            }));

            if ($params && is_array($params)) {

                $params['appname'] = self::$application->getApplicationName();

                return $twig->render(str_replace(":", "/", $view), $params);
            }

            $params['appname'] = self::$application->getApplicationName();

            return $twig->render(str_replace(":", "/", $view, $params));


        } else {

            $page_name = $view;

            return $this->render("message/page_not_found.html.twig", array('page_name' => $page_name));
        }
    }

    public function completeRender($view, $params = null) {

        if (file_exists(self::VIEW_PATH . str_replace(":", "/", $view))) {

            $twig = new Twig_Environment(new Twig_Loader_Filesystem('views'), array('cache' => false));
            $twig->addFunction(new \Twig_SimpleFunction('path', function($file = null) {
                if ($file == null) {
                    return "http://localhost" . self::$application->getSessionDefDir();
                }
                return "http://localhost" . self::$application->getSessionDefDir() . $file;
            }));
            $twig->addFunction(new \Twig_SimpleFunction('url', function($route = null, $options = null) {
                if ($route === null) {

                    throw new \RuntimeException("Veuillez passer le nom de la route");
                }
                try {
                    return Router::generateURL($route, $options, "_", $this->getTruncatedReferer());
                } catch(\Exception $e) {

                    die ($e->getMessage());
                }
            }));
            $twig->addGlobal("session", self::$application->getSession());
            $twig->addGlobal("md", self::$application->get('parsedown'));
            $twig->addGlobal("tm", self::$application->get('timing'));
            $twig->addGlobal("url", self::$application->get('serv.url'));
            $twig->addGlobal("trans", new Translator());

            $twig->addFunction(new \Twig_SimpleFunction('view', function($route_name, $options = null) {

                Router::view($route_name, $options);
            }));

            $twig->addFunction(new \Twig_SimpleFunction("loader", function() {
                return '<span style="visibility:hidden"
                                                                                       class="loader pull-right"><img
                        src="' . $this->getTruncatedReferer() . self::$application->getSessionDefDir() . 'views/lib/img/loader.gif" alt="loader"/></span>';
            }));

            $twig->addFunction(new \Twig_SimpleFunction('status', function($status) {
                $st_array = array('12.png', '10.png');
                return '<img
                        src="' . $this->getTruncatedReferer() .  self::$application->getSessionDefDir() . 'views/lib/img/icones/' . $st_array[$status] . '"/>';
            }));

            $twig->addFunction(new \Twig_SimpleFunction('change_content', function($href, $save_history = true, $span = null, $text = null) {
                return ' onclick="_system.select_type(\'' . $href . '\', ' . $save_history . '); return false';
            }));

            if ($params && is_array($params)) {

                $params['appname'] = self::$application->getApplicationName();

                return $twig->render(str_replace(":", "/", $view), $params);
            }

            $params['appname'] = self::$application->getApplicationName();

            return $twig->render(str_replace(":", "/", $view, $params));


        } else {

            $page_name = $view;

            return $this->render("message/page_not_found.html.twig", array('page_name' => $page_name));
        }
    }
    
    public function renderView($view, $params = null) {
        
        ob_start();
        
        echo $this->render($view, $params);
        
        $content = ob_get_clean();
        
        return $content;
    }

    public function getTruncatedReferer() {
        return substr($_SERVER["HTTP_REFERER"], 0, strpos($_SERVER["HTTP_REFERER"], self::$application->getSessionDefDir()));
    }

    public function redirect($page) {
        
        header('location: ' . $page);
    }
    
    public function generateURL($route_name, $options = null) {
        
        return Router::generateURL($route_name, $options);
    }

    public function __construct() {
        if (!self::$application instanceof Application) {
            self::$application = new Application();
        }
    }
}