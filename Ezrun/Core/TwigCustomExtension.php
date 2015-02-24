<?php
namespace Ezrun\Core;

use Ezrun\Core\Translation\TwigTranslation;

class TwigCustomExtension extends \Twig_Extension {
    
    protected $parameters;
    protected $translation;
    protected $use_cookie;
    
    public function __construct(array $parameters, $use_cookie = true) {
        
        $this->setParameters($parameters);
        $this->setUseCookie($use_cookie);
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
        
        $this->transaltion = new TwigTranslation($this->getUseCookie());
        
        return $this;
    }
    
    public function getTranslation() {
        
        return $this->transaltion;
    }
    
    public function setUseCookie($use_cookie) {
        
        $this->use_cookie = $use_cookie;
        
        return $this;
    }
    
    public function getUseCookie() {
        
        return $this->use_cookie;
    }
    
    public function getFunctions() {
        
        return array(
            new \Twig_SimpleFunction('trans', array(
                $this->getTranslation(),
                'translate'
            )),
        );
    }
    
    public function getFilters() {
        
        return array(
            new \Twig_SimpleFilter('trans', array(
                $this->getTranslation(),
                'translate'
            )),
        );
    }
}
