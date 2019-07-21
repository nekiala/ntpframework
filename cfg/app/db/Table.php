<?php

/**
 * Description of Table
 *
 * @author Kiala
 */
class Table extends Annotation
{
    //put your code here
}

class Column extends Annotation
{

    public $name;
    public $type;
    public $nullable;
    public $length = 45;
    public $primary;
    public $skip = false;
    public $multiple = false;
    public $default;


    public function checkConstraints($target)
    {

        if (!is_string($this->name)) {

            throw new Exception("Le nom doit être une chaine de caractère");
        }

        if (!preg_match("`^[\d]+$`", $this->length)) {

        }
    }
}

class Relation extends Annotation
{

    public $target;
    public $column;
    public $multiple = false;
    public $nullable = false;
}

class Populate extends Annotation
{

    public $target;
    public $column;
}

class Id extends Annotation
{
}

class HasLiceCycle extends Annotation
{
}

class View extends Annotation
{
}

class NoId extends Annotation
{
}