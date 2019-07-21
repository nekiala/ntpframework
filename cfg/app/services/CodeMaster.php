<?php
/**
 * Created by PhpStorm.
 * User: nekia_000
 * Date: 10/2/2015
 * Time: 3:46 PM
 */

namespace cfg\app\services;


class CodeMaster
{
    private $manager;
    
    public function __construct($manager = null) {
        if (!is_null($manager) && is_object($manager) && strstr(get_class($manager), "Manager")) {
            $this->manager = $manager;
        }
    }

    /**
     * @return mixed
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * @param mixed $manager
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    }
    
    public function generate($column = null, $limit = 8) {
        
        if ($this->manager) {
            static $count = 10;

            $chain = "abcdefghijklmnopqrqtuvwxyz1234567890";

            $code =  strtoupper(substr(str_shuffle($chain), 0, $limit));

            if ($this->manager->codeExist($code, $column)) {
                $count--;

                if ($count == 0) {
                    throw new \RuntimeException("Cannot create that code. Please press F8 to refresh the page. If it persist, try again later");
                }

                return $this->generate();
            }

            return $code;
        }
    }
    
}