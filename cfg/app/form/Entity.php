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
 * Description of Entity
 *
 * @author Kiala Ntona <kiala@ntoprog.org>
 */
class Entity implements \ArrayAccess {

    protected $erreurs = array(), $id;

    public function __construct(array $donnees = array()) {
        if (!empty($donnees)) {
            $this->hydrate($donnees);
        }
    }

    public function isNew() {
        return empty($this->id);
    }

    public function getErreurs() {
        return $this->erreurs;
    }

    public function getId() {
        return $this->id;
    }

    public function hydrate(array $donnees) {
        foreach ($donnees as $attribut => $valeur) {
            $methode = 'set' . ucfirst($attribut);
            if (is_callable(array($this, $methode))) {
                $this->$methode($valeur);
            }
        }
    }

    public function setId($id) {
        $this->id = (int) $id;
    }

    public function offsetExists($offset) {

        return isset($this->$offset) && is_callable(array($this, $offset));
    }

    public function offsetGet($offset) {

        if (isset($this->$offset) && is_callable(array($this, $offset))) {
            return $this->$offset();
        }
    }

    public function offsetSet($offset, $value) {

        $method = 'set' . ucfirst($offset);
        if (isset($this->$offset) && is_callable(array($this, $method))) {
            $this->$method($value);
        }
    }

    public function offsetUnset($offset) {

        throw new \Exception('Impossible de supprimer une quelconque valeur');
    }

}
