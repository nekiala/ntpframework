<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace cfg\app\services;

/**
 * Cette classe me permet de manipuler le port serie
 * directement via PHP
 *
 * @author Kiala
 */
class Serial {
    
    const SERIAL_DEVICE_NOTSET = 0;
    const SERIAL_DEVICE_SET = 1;
    const SERIAL_DEVICE_OPENED = 2;
    
    private $device;
    private $baud;
    private $parity;
    private $length;
    private $bits;
    private $flow_control;
    private $system_name;
    private $os;
    private $handler;

    private $device_state = self::SERIAL_DEVICE_NOTSET;

    public function setDevice($device) {
        
        if ($this->device_state !== self::SERIAL_DEVICE_OPENED) {
            
            $matches = null;
            
            if ($this->os === "windows") {
                
                if (preg_match("`^COM(\d+):?$`i", $device, $matches)) {
                    
                    $this->device = "COM" . $matches[1];
                    $this->device_state = self::SERIAL_DEVICE_SET;
                    
                    return $this;
                } else {
                    throw new \RuntimeException("Ce port n'existe pas.");
                }
            } else {
                throw new \RuntimeException("Windows s'il vous plaît.");
            }
        } else {
            throw new \RuntimeException("Le port semble déjà être ouvert.");
        }
    }
    
    public function openDevice($mode = "r+b") {
        
        if ($this->device_state === self::SERIAL_DEVICE_OPENED) {
            
            throw new \RuntimeException("Le port semble déjà être ouvert.");
        }
        
        if ($this->device_state === self::SERIAL_DEVICE_NOTSET) {
            
            throw new \RuntimeException("Vous devez d'abord indiquer le port avant de l'ouvrir.");
        }
        
        if (!preg_match("`^[raw]\+?b?$`", $mode)) {
            
            throw new \RuntimeException("Le mode d'ouverture précisé n'existe pas.");
        }
        
        $handler = @fopen($this->device, $mode);
        
        try {
            $this->setHandler($handler);
        } catch (\Exception $ex) {
            die ($ex->getMessage());
        }
    }
    
    public function closeDevice() {
        
        if ($this->device_state !== self::SERIAL_DEVICE_OPENED) {
            
            return true;
        }
        
        if (fclose($this->handler)) {
            
            $this->handler = null;
            $this->device_state = self::SERIAL_DEVICE_SET;
            
            return true;
        }
    }
    
    public function getDevice() {
        return $this->device;
    }
    
    public function setBaud($baud) {
        $this->baud = $baud;
        return $this;
    }
    
    public function getBaud() {
        return $this->baud;
    }
    
    public function setParity($parity) {
        $this->parity = $parity;
        return $this;
    }
    
    public function getParity() {
        return $this->parity;
    }
    
    public function setLength($length) {
        $this->length = $length;
        return $this;
    }
    
    public function getLength() {
        return $this->length;
    }
    
    public function setBits($bits) {
        $this->bits = $bits;
        return $this;
    }
    
    public function getBits() {
        return $this->bits;
    }
    
    public function setFlowControl($flow_control) {
        $this->flow_control = $flow_control;
        return $this;
    }
    
    public function getFlowControl() {
        return $this->flow_control;
    }
    
    public function setSystemName($system_name) {
        $this->system_name = $system_name;
        return $this;
    }
    
    public function getSystemName() {
        return $this->system_name;
    }
    
    public function setOs($os) {
        $this->os = $os;
        return $this;
    }
    
    public function getOs() {
        return $this->os;
    }
    
    public function setHandler($handler) {
        if ($handler) {
            
            $this->handler = $handler;
            stream_set_blocking($this->handler, 0);
            $this->device_state = self::SERIAL_DEVICE_OPENED;
            
            return $this;
        } else {
            throw new \Exception("Impossible de récupérer la ressource.");
        }
    }
    
    public function getHandler() {
        return $this->handler;
    }
    
    public function __construct() {
        
        $this->system_name = strtolower(substr(php_uname('s'), 0, 3));
        
        if ($this->system_name == "win") {
            $this->os = "windows";
        } else {
            throw new Exception("Le système n'est pas compatible sous un autre OS que Windows");
        }
    }
}
