<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace cfg\app\services;
use models\SystemTypeParameter;

/**
 * Description of Form
 *
 * @author Kiala
 */
class Form {
    
    public function extractParams(array $params) {
        
        $out = array();
        
        foreach ($params as $param) {
            
            $array = explode('_', $param);
            
            $out[] = array(
                //cle      //name     //value
                $array[0], $array[1], $array[2]
            );
        }
        
        return $out;
        
    }
    
    public function build(SystemTypeParameter $param) {
        
    }
} 
