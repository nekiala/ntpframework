<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace cfg\app\services;

use cfg\app\Application;

/**
 * Description of File
 *
 * @author Kiala
 * @version 2
 */
class File
{

    //put your code here

    private $fileGlobal;

    private $unauthorized_ext = array(
        '.bat', '.exe', '.inf', '.php', '.html',
        '.css', '.htm', '.js', '.vbi', '.pl', '.msi', '.vba'
    );

    private $authorized_ext = array(
        'jpeg', 'png', 'gif', 'jpg'
    );

    public function setUnauthorised(array $list)
    {
        $this->unauthorized_ext = $list;
        return $this;
    }

    public function setAuthorized(array $list)
    {
        $this->authorized_ext = $list;
        return $this;
    }

    /**
     * remplace les caractères spécieux à leurs équivalents
     *
     * @param string $filename le nom du fichier
     * @return string
     */
    public function replacements($filename)
    {

        $match = array('é', '&', 'è', 'ç', 'à', 'î', 'ô', 'ê', 'ï', 'ö', 'ë', ';', ',', '~', ' ', '^', '¨', '\'', '{', '}');
        $replace = array('e', '_', 'e', 'c', 'a', 'i', 'o', 'e', 'i', 'o', 'e', '_', '_', '_', '_', '_', '_', '_', '_', '_');

        return str_replace($match, $replace, $filename);
    }

    public function generateName($filename)
    {
        return sha1(strtolower($filename));
    }

    public function formatName($filename)
    {
        $file = strtolower($filename);

        return str_shuffle(time() . '_' . $file);
    }

    /**
     * retourne un fichier renommé
     *
     * @param string $key
     * @return string
     */
    public function fileGenerated($key)
    {

        return $this->generateName($this->getTemp($key)) . '.' . $this->getExtension($key);
    }

    public function normalFileGenerated($key)
    {
        return $this->formatName($this->replacements($this->getFilename($key)));
    }

    /**
     * retourne le nom du fichier dont la clé se trouve en paramètre
     *
     * @param string $key clé à rechercher
     *
     * @return string nom du fichier
     */
    public function getFilename($key)
    {

        $filename = $this->fileGlobal[$key]['name'];

        return $filename;
    }

    /**
     * Retourne l'extension du fichier
     *
     * @param string $key
     * @return string
     */
    public function getExtension($key)
    {

        $file = $this->fileGlobal[$key]['name'];

        Application::$request_log->setMessage("FILENAME: " . $file)->notify();

        return strtolower(substr($file, strrpos($file, '.')));
    }

    public function getNaturalExtension($key)
    {
        $file = explode('/', $this->fileGlobal[$key]['type']);

        return $file[1];
    }

    public function getType($key)
    {
        return $this->fileGlobal[$key]['type'];
    }

    /**
     * Permet de retrouver une clée dans le $fileGlobal, qui est
     * une représentation de $_FILES
     *
     * @param string $key la clé à rechercher dans le global $fileGlobal
     * @return boolean
     */
    public function keyExists($key)
    {

        return isset($this->fileGlobal[$key]);
    }

    /**
     * vérifie si la clé $_FILES existe
     *
     * @return boolean
     */
    public function globalExists()
    {

        return isset($this->fileGlobal);

    }

    public function noEmptyGlobals($key)
    {
        $original_name = $this->getOriginalName($key);

        return ($this->globalExists() && $original_name != "");
    }

    /**
     * retourne la taille du fichier
     *
     * @param string $filename
     * @return int
     */
    public function getFileSize($filename)
    {

        return filesize($filename);
    }

    /**
     * vérifie la taille de $filesize
     * renvoi TRUE si elle est supérieure à $size, et FALSE dans le cas
     * contraire
     *
     * @param int $file_size taille du fichier
     * @param int $size taille à comparer
     * @return boolean
     */
    public function isSize($file_size, $size)
    {

        return $file_size < $size;
    }

    public function getTemp($key)
    {
        return $this->fileGlobal[$key]['tmp_name'];
    }

    public function getOriginalName($key)
    {
        return isset($this->fileGlobal[$key]['name']) ? $this->fileGlobal[$key]['name'] : false;
    }

    public function getSize($key)
    {
        return $this->fileGlobal[$key]['size'];
    }

    public function upload($key)
    {
        $logo_name = $this->fileGenerated($key);

        Application::$request_log->setMessage("LOGO NAME FOR KEY " . $key . ": " . $logo_name)->notify();

        if (move_uploaded_file($this->getTemp($key), Application::$system_files->getUploadDirectory() . '/' . $logo_name)) {
            return $logo_name;
        }

        return false;
    }

    public function delete($name)
    {

        if (file_exists(Application::$system_files->getUploadDirectory() . "/" . $name)) {
            unlink(Application::$system_files->getUploadDirectory() . "/" . $name);
            Application::$request_log->setMessage("The file {$name} was deleted")->notify();
        } else {
            Application::$request_log->setMessage("The file {$name} do not exist")->notify();
        }
    }

    public function __construct()
    {

        if (isset($_FILES)) {

            $this->fileGlobal = $_FILES;
        }
        return $this;
    }

}
