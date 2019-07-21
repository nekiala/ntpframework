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

namespace cfg\app\form;

use cfg\app\form\Field;

/**
 * Description of TextareaField
 *
 * @author Kiala Ntona <kiala@ntoprog.org>
 */
class TextareaField extends Field {

    protected $cols;
    protected $rows;

    public function buildWidget() {
        $widget = '';
        if (!empty($this->errorMessage)) {
            $widget .= $this->errorMessage . '<br />';
        }
        $widget .= '<label class="control-label">' . $this->label . '</label><textarea
name="' . $this->name . '" class="form-control"';
        if (!empty($this->cols)) {
            $widget .= ' cols="' . $this->cols . '"';
        }
        if (!empty($this->rows)) {
            $widget .= ' rows="' . $this->rows . '"';
        }
        $widget .= '>';
        if (!empty($this->value)) {
            $widget .= htmlspecialchars($this->value);
        }
        return $widget . '</textarea>';
    }

    public function setCols($cols) {
        
        $_cols = (int) $cols;
        if ($_cols > 0) {
            $this->cols = $_cols;
        }
    }

    public function setRows($rows) {
        
        $_rows = (int) $rows;

        if ($_rows > 0) {
            $this->rows = $_rows;
        }
    }

}
