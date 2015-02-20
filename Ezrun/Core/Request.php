<?php
namespace Ezrun\Core;

class Request extends BaseCore {
    
    protected $method;
    protected $original_method;
    protected $parameters   = array();
    protected $server       = array();
    
    public function __construct($method = 'GET', $request_parameters = array()) {
        
        $this->setMethod($method);
        $this->setParameters($request_parameters);
    }
    
    public function setOriginalMethod() {
        
        $this->original_method = strtoupper($_SERVER['REQUEST_METHOD']);
        
        return $this;
    }
    
    public function getOriginalMethod() {
        
        return $this->original_method;
    }
    
    public function setMethod($method) {
        
        $this->setOriginalMethod();
        
        $this->method = strtoupper($method);
        
        return $this;
    }
    
    public function getMethod() {
        
        return $this->method;
    }
    
    public function setParameters($request_parameters) {
        
        $this->parameters = $request_parameters;
        
        return $this;
    }
    
    public function getParameters() {
        
        return $this->parameters;
    }
}