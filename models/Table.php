<?php
/**
 * Created by PhpStorm.
 * User: KIALA
 * Date: 09/08/2016
 * Time: 21:35
 */

namespace models;

/**
 * Class Table
 * @package models
 * @Table("campaign")
 */
class Table
{
    /**
     * @var integer
     * @Column(name="id", skip=true)
     * @Id
     */
    private $id;
    /**
     * @var string
     * @Column(name="code")
     */
    private $code;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }


}