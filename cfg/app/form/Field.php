<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace cfg\app\form;

/**
 * Description of Field
 *
 * @author Kiala
 */
abstract class Field {

    //put your code here

    protected $errorMessage;
    protected $label;
    protected $name;
    protected $value;
    protected $css_class;

    public function __construct(array $options = array()) {
        if (!empty($options)) {
            $this->hydrate($options);
        }
    }

    abstract public function buildWidget();

    public function hydrate($options) {
        foreach ($options as $type => $value) {
            $method = 'set' . ucfirst($type);
            if (is_callable(array($this, $method))) {
                $this->$method($value);
            }
        }
    }

    public function isValid() {
// On écrira cette méthode plus tard.
    }

    public function getLabel() {
        return $this->label;
    }

    public function getName() {
        return $this->name;
    }

    public function getValue() {
        return $this->value;
    }

    public function setLabel($label) {
        if (is_string($label)) {
            $this->label = $label;
        }
    }

    public function setName($name) {
        if (is_string($name)) {
            $this->name = $name;
        }
    }

    public function setValue($value) {
        if (is_string($value)) {
            $this->value = $value;
        }
    }
    
    public function setCSSClass($css_class) {
        $this->css_class = $css_class;
        return $this;
    }
    
    public function getCSSClass() {
        return $this->css_class;
    }

}
