<?php

namespace cfg\app;

use cfg\app\db\Connector;
use cfg\app\db\DBInterface;
use cfg\app\observers\LogHandler;
//use cfg\app\services\Wkhtmltopdf;

class Controller extends Application {

    private static $connector = null;
    protected $response;
    protected $log;
    protected $url;

    /**
     * @var object
     */
    protected $manager;

    public function __construct($manager = null) {
        parent::__construct(true);
        $this->response = new Response();
        $this->log = new LogHandler();

        if (!is_null($manager)) {
            $this->manager = $this->getManager($manager);
        }

        $this->url = $this->get("serv.url");
    }

    /**
     * @return Connector
     */
    public function getConnector()
    {
        if (is_null(self::$connector)) {
            self::$connector = new Connector();
        }

        return self::$connector;
    }

    public function getModel($model) {

        $class = Application::$system_files->getModelsNamespace() . $model;
        return new $class();
    }

    /**
     * @param null $manager
     * @return DBInterface | object
     */
    public function getManager($manager = null) {

        if (is_null($manager) && $this->manager != null) {

            return $this->manager;
        }

        if (strpos($manager, "\\")) {

            $manager = ucfirst(substr($manager, strpos($manager, "\\") + 1));
        }

        return $this->getConnector()->getManager(str_replace("models\\", "", $manager));
    }

    public function generateURL($route_name, $options = NULL) {
        return $this->response->generateURL($route_name, $options);
    }

    public function render($view, $params = null) {
        return $this->response->render($view, $params);
    }

    public function completeRender($view, $params = null) {
        return $this->response->completeRender($view, $params);
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return $this->request;
    }

    public function redirect($page) {
        $this->response->redirect($page);
    }

    public function logout() {

        $session = $this->getSession();
        $this->getConnector()->removeDBStructure();
        $session->destroy();
    }

    public function getController($controller) {

        $controller_class_name = Application::$system_files->getControllersNamespace() . $controller . "Controller";

        if (class_exists($controller_class_name)) {

            return new $controller_class_name();
        }

        die("Class not² found");
    }

    public function getUserRole() {
        
        $session = $this->getSession();
        $user = $session->decode();
        $role = unserialize($session->get('usr_roles'));

        try {
            $user_role = $this->getManager('UserRole')->findWithClause(array(
                'user_id' => $user->getId(), 'role_id' => $role->getId()
            ), "AND");

        } catch (\Exception $e) {

            die($e->getMessage());
        }

        return $user_role;
    }

    public function getUserRoleClass() {

        $session = $this->getSession();

        $user = $session->decode();
        $role = unserialize($session->get('usr_roles'));

        return array($user, $role);
    }

    /**
     * return the active user role
     * @return mixed
     */
    public function getUserActiveRole() {
        $session = $this->getSession();

        return unserialize($session->get('usr_roles'));
    }

    protected function getNumberFromURLString($str) {

        try {
            return $this->get("serv.url")->transformToNumber($str);
        } catch (\Exception $e) {

            die($e->getMessage());
        }
    }

    protected function getStringFromURLNumber($num) {

        try {
            return $this->get("serv.url")->transformToStr($num);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    protected function decodeUrl($str)
    {
        try {
            return $this->get("serv.url")->decodeUrl($str);
        } catch (\Exception $e) {

            die($e->getMessage());
        }
    }

    protected function encodeUrl($str)
    {
        try {
            return $this->get("serv.url")->encodeUrl($str);
        } catch (\Exception $e) {

            die($e->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function findUser($id) {

        $user_id = $this->getNumberFromURLString($id);

        if ($user_id == $this->getSession()->getUser()->getId()) {

            $user = $this->getSession()->getUser();

        } else {

            //same class
            if (strstr(strtolower(get_class($this->manager)), "user")) {

                $user = $this->manager->find($user_id);

            } else {

                //different class
                $user = $this->getManager("User")->find($user_id);
            }
        }

        return $user;
    }

    public function getOffset($actual_page, $number_per_page, $total_count) {

        $page_number = ceil($total_count / $number_per_page);

        $offset = ($actual_page - 1) * ($page_number);

        return $offset;
    }

    protected function switchView($normal_view, $xhr_view) {
        return ($this->request->isXHR()) ? $xhr_view : $normal_view;
    }

    protected function pickView($normal_view) {

        return ($this->request->isXHR()) ? $normal_view . Application::XHR_TERMINATION : $normal_view;
    }

    protected function pView($normal_view) {

        if (!strpos($normal_view, ".")) {
            $normal_view .= ".html.twig";
        }

        if (strstr($normal_view, ".html.twig")) {

            $extension = substr($normal_view, strpos($normal_view, "."));
            $name = substr($normal_view, 0, strpos($normal_view, "."));
            $final_name = $this->pickView($name);

            return $final_name.$extension;
        }

        return $normal_view;
    }

    protected function ifXHR() {

        return $this->request->isXHR();
    }

    protected function explodeParameter($param, $delimiter = "_", $return = 1)
    {

        $array = explode($delimiter, $param);

        return $array[$return];
    }

    protected final function downloadParameterFile($filename) {

        $file = Application::$system_files->getUploadDirectory() . "/" . $filename;

        if (file_exists($file)) {


            $mime_types = array(
                "jpg" => "image/jpeg",
                "jpeg" => "image/jpeg",
                "png" => "image/png",
                "gif" => "image/gif",
                "pdf" => "application/pdf",
            );

            $file_extension = substr($filename, strrpos($filename, ".") + 1);

            $out = time() . "." . $file_extension;

            header("Content-Type: " . $mime_types[$file_extension]);
            header('Content-Disposition: attachment; filename=' . $out);

            readfile($file);
        }
    }

    protected final function uploadParameterFile($filename) {
        try {
            $file = $this->get("serv.file");

        } catch (\Exception $e) {

            die($e->getMessage());
        }

        return $file->upload($filename);
    }

    public function __call($action, $arguments) {

        return $this->pView("message:page_not_found.html.twig");
    }

    /**
     * @param $message
     * @param bool $xhr
     * @return string
     */
    private function notifyException($message, $xhr = false)
    {
        if (!$xhr) {

            return $this->render($this->pView("message:exception_handler.html.twig"), [
                "message" => $message
            ]);

        }

        return $this->render("message:exception_handler.html.twig", [
            "message" => $message
        ]);
    }

    public function notifyPageNotExist()
    {
        $message = "Cette page n'existe pas ou est indisponible pour l'instant.";

        return $this->notifyException($message);
    }

    public function checkSystemEnabled()
    {
        $session = $this->getSession();
        $user = $session->decode();

        if (!$system_id = $user->getSystem()->getId()) {

            return false;
        }

        return $system_id;
    }

    public function notifySystemDisabled()
    {
        $message = "Votre licence est expirée ou soit elle n'a pas été activée.";

        return $this->notifyException($message);
    }

    protected function pdfLink()
    {
        $system_files = json_decode(file_get_contents(Application::$system_files->getApplicationFile()), true);

        return $system_files['pdf_url'];
    }

    public final function getPCName()
    {
        $ip = getenv("HTTP_CLIENT_IP") ?:
            getenv("HTTP_X_FORWARDED_FOR")?:
                getenv("HTTP_FORWARDED_FOR")?:
                    getenv("HTTP_FORWARDED_FOR") ?:
                        getenv("HTTP_FORWARDED")?:
                            getenv("REMOTE_ADDR");

        return gethostbyaddr($ip);
    }

    public function prepareXmlHeader()
    {
        $str = <<<KIALA
<?xml version="1.0" encoding="UTF-8" ?>
KIALA;
        return $str;
    }

    public final function roleDeletable()
    {
        return $this->get("Session")->role()->isDeletable();
    }

    public final function roleWritable()
    {
        return $this->get("Session")->role()->isWritable();
    }

    public final function roleEditable()
    {
        return $this->get("Session")->role()->isEditable();
    }

    public final function roleReadable()
    {
        return $this->get("Session")->role()->isReadable();
    }

    public function notifyAuthorizationInsufficientForRead()
    {
        $message = "Vous n'avez pas l'autirisation de lire ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message);
    }

    public function notifyAuthorizationInsufficientForReadXHR()
    {
        $message = "Vous n'avez pas l'autirisation de lire ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message, true);
    }

    public function notifyAuthorizationInsufficientForDelete()
    {
        $message = "Vous n'avez pas l'autirisation de supprimer ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message);
    }

    public function notifyAuthorizationInsufficientForDeleteXHR()
    {
        $message = "Vous n'avez pas l'autirisation de supprimer ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message, true);
    }

    public function notifyAuthorizationInsufficientForEdit()
    {
        $message = "Vous n'avez pas l'autirisation de modifier ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message);
    }

    public function notifyAuthorizationInsufficientForEditXHR()
    {
        $message = "Vous n'avez pas l'autirisation de modifier ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message, true);
    }

    public function notifyAuthorizationInsufficientForWrite()
    {
        $message = "Vous n'avez pas l'autirisation de créer ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message);
    }

    public final function notifyAuthorizationInsufficientForWriteXHR()
    {
        $message = "Vous n'avez pas l'autirisation de créer ce contenu.
        Veuillez contacter votre administrateur";

        return $this->notifyException($message, true);
    }

    protected function getLocale()
    {
        return $this->getSession()->get("locale");
    }

    protected function setLocale($locale)
    {
        $this->getSession()->save("locale", $locale);
    }

    public final function getDefaultLang()
    {
        return $_SERVER["HTTP_ACCEPT_LANGUAGE"];
    }
}
