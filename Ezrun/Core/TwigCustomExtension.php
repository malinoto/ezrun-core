<?php
namespace Ezrun\Core;

class TwigCustomExtension extends \Twig_Extension {
    
    protected $paramters;
    
    public function __construct(array $parameters) {
        
        $this->setParameters($parameters);
    }
    
    public static function BaseCore() {
    }
    
    public static function TwigCustomExtension() {
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
    
    public function getFunctions() {
        
        return array(
            new \Twig_SimpleFunction('form_head', array($this, 'renderFormHead')),
        );
    }
}
