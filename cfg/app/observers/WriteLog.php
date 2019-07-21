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
use cfg\app\Application;

/**
 * Description of WriteLog
 *
 * @author Kiala Ntona <kiala@ntoprog.org>
 */
class WriteLog implements \SplObserver {
    
    public function write($message) {

        //update on 2016-03-07 14:14
        $log_file = sprintf("%s_%d_%d_%d.NTPLog", Application::$system_files->getLogFile(), date('Y'), date('m'), date('d'));

        if (!$file = @fopen($log_file, 'a+')) {
            throw new \Exception("Cannot open file " . Application::$system_files->getLogFile());
        }

        flock($file, LOCK_EX);
        fputs($file, $message, strlen($message));
        flock($file, LOCK_UN);
        fclose($file);
    }
    
    public function update(\SplSubject $subject) {
        
        $this->write($subject->formatedMsg());
    }

}
