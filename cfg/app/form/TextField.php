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
 * Description of TextField
 *
 * @author Kiala Ntona <kiala@ntoprog.org>
 */
class TextField extends Field {

    protected $maxLength;

    public function buildWidget() {
        $widget = '';
        if (!empty($this->errorMessage)) {
            $widget .= $this->errorMessage . '<br />';
        }
        $widget .= '<label class="control-label">' . $this->label . '</label><input type="text" name="' . $this->name . '"';
        if (!empty($this->value)) {
            $widget .= ' value="' . htmlspecialchars($this->value) . '"';
        }
        if (!empty($this->maxLength)) {
            $widget .= ' maxlength="' . $this->maxLength . '"';
        }
        return $widget .= ' />';
    }

    public function setMaxLength($maxLength) {
        $_maxLength = (int) $maxLength;
        if ($_maxLength > 0) {
            $this->maxLength = $_maxLength;
        } else {
            throw new \RuntimeException('La longueur maximale doit être un nombre supérieur à 0');
        }
    }

}
