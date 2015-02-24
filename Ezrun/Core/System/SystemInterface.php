<?php

namespace Ezrun\Core\System;

interface SystemInterface {
    
    public function executeCommand();
    
    public function setCommand($command);
    
    public function getCommand();
    
    public function setConfigParser($config_parser);
    
    public function getConfigParser();
}