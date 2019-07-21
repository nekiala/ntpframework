<?php

namespace cfg\app;

use cfg\app\db\Connector;
use cfg\app\db\DBInterface;
use cfg\app\observers\LogHandler;
use cfg\app\services\Prince;
//use cfg\app\services\Wkhtmltopdf;

class Controller extends Application {

    private static $connector = null;
    protected $response;
    protected $log;

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

        throw new \Exception("Class not found");
    }

    public function getUserRole() {
        
        $session = $this->getSession();
        $user = $session->decode();
        $role = unserialize($session->get('usr_roles'));

        $user_role = $this->getManager('UserRole')->findWithClause(array(
            'user_id' => $user->getId(), 'role_id' => $role->getId()
        ), "AND");
        
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

        return $this->get("serv.url")->transformToNumber($str);
    }

    protected function getStringFromURLNumber($num) {

        return $this->get("serv.url")->transformToStr($num);
    }

    /**
     * @param $id
     * @return mixed
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

    protected function getPDF() {
        $prince = new Prince("C:\prince-10r5-win32\bin\prince.exe");

        return $prince;
    }

    protected function printPDF($file_string, $filename) {

        $prince = $this->getPDF();

        $msg = array();

        $filename_html = $filename . ".html";
        $filename_pdf = $filename . ".pdf";

        $file_o = fopen($filename_html, "w+");
        fclose($file_o);
        file_put_contents($filename_html, $file_string);

        header("Content-Type: application/pdf");
        header('Content-Disposition: attachment; filename=' . time() . "_". $filename_pdf);

        $prince->setPDFAuthor("NtoProg");
        $prince->setPDFTitle($filename);
        $prince->setHTML("html");
        $prince->setJavaScript(true);
        $prince->setHttpUser("admin");
        $prince->setHttpPassword("secret");

        $prince->convert_file($filename_html, $msg);

        unlink($filename_html);

        readfile($filename_pdf);

        unlink($filename_pdf);
    }

    /*public function pdfPrint($file_string, $filename) {

        $filename_html = $filename . ".html";
        $filename_pdf = $filename . ".pdf";

        /*$file_o = fopen($filename_html, "w+");
        fclose($file_o);
        file_put_contents($filename_html, $file_string);

        try {
            $html_to_pdf = new Wkhtmltopdf(array('path' => 'C:\xampp\htdocs\Garage\views\storage'));

            $html_to_pdf->setTitle($filename);
            $html_to_pdf->setHtml($file_string);
            $html_to_pdf->output(Wkhtmltopdf::MODE_DOWNLOAD, $filename_pdf);
            
        } catch (\Exception $e) {

            die ($e->getMessage());
        }
    }*/


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
        $file = $this->get("serv.file");

        return $file->upload($filename);
    }

    public function __call($action, $arguments) {

        return $this->pView("message:page_not_found.html.twig");
    }

    protected function pdfLink()
    {
        $system_files = json_decode(file_get_contents(Application::$system_files->getApplicationFile()), true);

        return $system_files['pdf_url'];
    }
}
