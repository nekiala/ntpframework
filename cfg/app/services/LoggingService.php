<?php
/**
 * Created by PhpStorm.
 * User: Kiala Ntona
 * Date: 22/07/2019
 * Time: 12:26
 */

namespace cfg\app\services;


use models\Logging;
use models\manager\LoggingManager;

class LoggingService
{
    /**
     * @var Logging
     */
    private $logging;

    /**
     * LoggingService constructor.
     * @param string $action
     * @param string $username
     * @param string $message
     */
    public function __construct(string $action, string $username, string $message)
    {
        $this->logging = new Logging();

        $this->logging->setAction($action);
        $this->logging->setUsername($username);
        $this->logging->setMessage($message);

        return $this;
    }

    /**
     * @return Logging
     */
    public function getLogging(): Logging
    {
        return $this->logging;
    }

    /**
     * @param Logging $logging
     */
    public function setLogging(Logging $logging): void
    {
        $this->logging = $logging;
    }

    public function log(?bool $unset = true)
    {
        $loggingManager = new LoggingManager();

        $loggingManager->persist($this->logging);

        if ($unset) $this->flush();
    }

    public function flush()
    {
        unset($this->logging);
    }
}