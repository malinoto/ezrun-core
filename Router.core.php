<?php
namespace Core;

class Router extends BaseCore {
    
    protected $routing;
    
    public function __construct(array $routing) {
        
        $this->setRouting($routing);
    }
    
    public function setRouting($routing) {
        
        $this->routing = $routing;
        
        return $this;
    }
    
    public function getRouting() {
        
        return $this->routing;
    }
}
