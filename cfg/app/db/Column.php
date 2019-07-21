<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Column
 *
 * @author Kiala
 */
class Column extends Annotation {
    
    public $name;
    public $type;
    public $nullable;
    public $length;
    public $primary;
    
    public function checkConstraints($target) {
        
        if (!is_string($this->name)) {
            
            throw new Exception("Le nom doit être une chaine de caractère");
        }
        
        if (!preg_match("`^[\d]+$`", $this->length)) {
            
        }
    }
}
