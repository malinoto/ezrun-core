<?php

namespace Ezrun\Core\System;

abstract class SystemAbstract implements SystemInterface {
    
    protected $config_parser;
    protected $command;
    
    public function __construct(\ConfigParser $config_parser, $command) {
        
        $this->setConfigParser($config_parser);
        $this->setCommand($command);
    }
    
    abstract public function executeCommand();
    
    public function setCommand($command) {
        
        $this->command = $command;
        
        return $this;
    }
    
    public function getCommand() {
        
        return $this->command;
    }
    
    public function setConfigParser($config_parser) {
        
        $this->config_parser = $config_parser;
        
        return $this;
    }
    
    public function getConfigParser() {
        
        return $this->config_parser;
    }
}