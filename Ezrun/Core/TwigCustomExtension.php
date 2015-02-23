<?php
namespace Ezrun\Core;

use Ezrun\Core\Translation\TwigTranslation;

class TwigCustomExtension extends \Twig_Extension {
    
    protected $parameters;
    protected $translation;
    
    public function __construct(array $parameters) {
        
        $this->setParameters($parameters);
        $this->setTranslation();
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
    
    public function setTranslation() {
        
        $this->transaltion = new TwigTranslation;
        
        return $this;
    }
    
    public function getTranslation() {
        
        return $this->transaltion;
    }
    
    public function getFunctions() {
        
        return array(
            new \Twig_SimpleFunction('trans', array($this->getTranslation(), 'translate')),
        );
    }
    
    public function getFilters() {
        
        return array(
            new \Twig_SimpleFilter('trans', array($this->getTranslation(), 'translate')),
        );
    }
}
