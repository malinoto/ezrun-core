<?php

namespace Ezrun\Core\Translation;

use Symfony\Component\Yaml\Yaml;

class Translation {
    
    protected $language;
    protected $labels           = array();
    protected $cookie_lifetime  = 31536000; //1 year - 60 * 60 * 24 * 365
    protected $use_cookie;
    
    public function __construct($use_cookie = true) {
        
        $this->setUseCookie($use_cookie);
        $this->setLanguage();
        $this->setTranslations();
    }
    
    public function addLabel($key, $value) {
        
        $this->labels[$key] = $value;
        
        return $this;
    }
    
    public function getLabels() {
        
        return $this->labels;
    }
    
    public function setTranslations() {
        
        $translation_file   = languages_path . '_' . $this->getLanguage() . '.yml';
        $translations       = array();
        
        if(is_file($translation_file))
            $translations = Yaml::parse($translation_file, true, true);
        
        foreach($translations as $key => $value) {
            
            $this->addLabel($key, $value);
        }
        
        return $this;
    }
    
    public function setLanguage() {
        
        if(isset($_COOKIE[language_cookie])) {
            
            $this->language = $_COOKIE[language_cookie];
        }
        else if($this->getUseCookie()) {
            
            setcookie(language_cookie, default_language, time() + $this->cookie_lifetime, "/", cookies_domain);
            $_COOKIE[language_cookie] = default_language;
        }
        
        return $this;
    }
    
    public function getLanguage() {
        
        return $this->language;
    }
    
    public function setUseCookie($use_cookie) {
        
        $this->use_cookie = $use_cookie;
        
        return $this;
    }
    
    public function getUseCookie() {
        
        return $this->use_cookie;
    }
}