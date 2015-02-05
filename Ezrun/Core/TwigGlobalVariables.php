<?php
namespace Ezrun\Core;

class TwigGlobalVariables extends \Twig_Extension {
    
    protected $paramters;
    
    public function __construct(array $parameters) {
        
        $this->setParameters($parameters);
    }
    
    public static function BaseCore() {
    }
    
    public static function TwigGlobalVariables() {
    }
    
    public function getName() {
        
        return 'TwigGlobalVariables_extension';
    }
    
    public function getGlobals() {
        
        $parameters = $this->getParameters();
        
        return isset($parameters['twig']) ? $parameters['twig'] : array();
    }
    
    public function setParameters($parameters) {
        
        $this->parameters = $parameters;
        
        return $this;
    }
    
    public function getParameters() {
        
        return $this->parameters;
    }

}
