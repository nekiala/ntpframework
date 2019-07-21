<?php

/*
 * Copyright 2015 Kiala Ntona <kiala@ntoprog.org>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace cfg\app\observers;

/**
 * Description of LogHandler
 *
 * @author Kiala Ntona <kiala@ntoprog.org>
 */
class LogHandler implements \SplSubject {

    protected $observers = array();
    protected $message;

    const TYPE_INFO = 1;
    const TYPE_WARNING = 2;
    const TYPE_ERROR = 3;
    const TYPE_EXCEPTION = 4;

    protected $type = self::TYPE_INFO;

    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function getMessage() {
        return $this->message;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function attach(\SplObserver $observer) {

        $this->observers[] = $observer;
        return $this;
    }

    public function detach(\SplObserver $observer) {

        if (is_int($key = array_search($observer, $this->observers, true))) {
            unset($this->observers[$key]);
        }
    }

    public function notify() {

        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    public function formatedMsg() {
        $date = date('d/m/Y H:i:s');
        $method = htmlspecialchars($_SERVER['REQUEST_METHOD']);
        $uri = htmlspecialchars($_SERVER['REQUEST_URI']);
        //$str = htmlspecialchars($_SERVER['QUERY_STRING']);
        $ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
        $message = $this->getMessage();

        switch ($this->type) {
            case self::TYPE_INFO:
                $type = "[INFO]";
                break;
            case self::TYPE_ERROR:
                $type = "[ERROR]";
                break;
            case self::TYPE_WARNING:
                $type = "[WARNING]";
                break;
            case self::TYPE_EXCEPTION:
                $type = "[EXCEPTION]";
                break;
            default:
                $type = "[INFO]";
                break;
        }

        $out = "$type : [{$date}] : IP \"{$ip}\" => METHOD HTTP/{$method} \"{$uri}\". ACTION : {$message}**\n";

        return $out;
    }

}
