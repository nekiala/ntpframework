<?php
/**
 * Created by PhpStorm.
 * User: KIALA
 * Date: 13/09/2016
 * Time: 00:37
 */

namespace models;

/**
 * Class Campaign
 * @package models
 * @Table("campaign")
 */
class Campaign
{
    /**
     * @var int
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
     * @var string
     * @Column(name="type")
     */
    private $type;

    /**
     * @var string
     * @Column(name="message")
     */
    private $message;

    /**
     * @var string
     * @Column(name="created_at", skip=true)
     */
    private $creation;

    /**
     * @var string
     * @Column(name="created_by")
     */
    private $creator;

    /**
     * @var string
     * @Column(name="created_from")
     */
    private $creating;

    /**
     * @var bool
     * @Column(name="status", skip=true)
     */
    private $status;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
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

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param string $creation
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    }

    /**
     * @return string
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param string $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @return string
     */
    public function getCreating()
    {
        return $this->creating;
    }

    /**
     * @param string $creating
     */
    public function setCreating($creating)
    {
        $this->creating = $creating;
    }

    /**
     * @return boolean
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }
}