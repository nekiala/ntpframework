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

/**
 * Description of Form
 *
 * @author Kiala Ntona <kiala@ntoprog.org>
 */
class Form {

    //put your code here
    protected $entity;
    protected $fields = array();

    public function __construct(Entity $entity) {
        $this->entity = $entity;
    }

    public function add(Field $field) {

        $attr = "get" . ucfirst($field->getName());
        $field->setValue($this->entity->$attr());

        $this->fields[] = $field;
        return $this;
    }

    public function createView() {
        $view = '';

        foreach ($this->fields as $field) {

            $view .= $field->buildWidget() . "<br />";
        }

        return $view;
    }

    public function isValid() {
        $valid = true;
    // On vérifie que tous les champs sont valides.
        foreach ($this->fields as $field) {
            if (!$field->isValid()) {
                $valid = false;
            }
        }
        return $valid;
    }

    public function getEntity() {
        return $this->entity;
    }

    public function setEntity(Entity $entity) {
        $this->entity = $entity;
    }

}
