<?php
/**
 * Created by PhpStorm.
 * User: Kiala Ntona
 * Date: 22/07/2019
 * Time: 12:27
 */

namespace models;

/**
 * Class Logging
 * @package models
 * @Table("logging")
 */
class Logging
{
    const UPDATE = "update";
    const CREATE = "create";
    const DELETE = "delete";

    /**
     * @var int
     * @Column(name="id", skip=true)
     * @Id
     */
    private $id;

    /**
     * @var string
     * @Column(name="username")
     */
    private $username;

    /**
     * @var string
     * @Column(name="action")
     */
    private $action;

    /**
     * @var string
     * @Column(name="message")
     */
    private $message;

    /**
     * @var string
     * @Column(name="created_at", skip=true)
     */
    private $created_at;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     */
    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }
}